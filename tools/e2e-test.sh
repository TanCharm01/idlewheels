#!/usr/bin/env bash
# end-to-end test against the local site (cleans up after itself)
set -u
B="${B:-http://localhost/idlewheels}"
MYSQL="${MYSQL:-/c/xampp/mysql/bin/mysql.exe -u root -h 127.0.0.1 -P 3307 idlewheels}"
PHP="${PHP:-/c/xampp/php/php.exe}"
PHOTO="${PHOTO:-$TEMP/iw-test-car.png}"
PASS="E2e-$(date +%s)-pass"   # throwaway, never printed
T=$(mktemp -d)
ok=0; fail=0
check() { if [ "$1" = "$2" ]; then echo "  PASS  $3"; ok=$((ok+1)); else echo "  FAIL  $3 (got '$1', want '$2')"; fail=$((fail+1)); fi; }
sql() { $MYSQL -N -e "$1"; }
token() { curl -s -b "$T/$1" -c "$T/$1" "$B$2" | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//'; }
post() { local jar=$1 path=$2; shift 2; curl -s -o /dev/null -w '%{http_code} %{redirect_url}' -b "$T/$jar" -c "$T/$jar" "$@" "$B$path"; }
flashes() { curl -s -b "$T/$1" -c "$T/$1" "$B$2" | grep -o 'class="flash flash-[a-z]*">[^<]*' | sed 's/.*">//'; }

cleanup() {
  local files
  files=$(sql "SELECT p.path FROM iw_car_photos p JOIN iw_cars c ON c.id = p.car_id JOIN iw_users u ON u.id = c.host_id WHERE u.email LIKE '%.e2e@idlewheels.test' AND p.path LIKE 'uploads/%';")
  sql "DELETE FROM iw_users WHERE email LIKE '%.e2e@idlewheels.test';"
  for f in $files; do rm -f "$f"; done
}
cleanup

echo "1. Accounts"
for who in host admin renter; do
  tk=$(token $who /register)
  post $who /register --data-urlencode "_token=$tk" -d "name=E2E ${who^}&email=$who.e2e@idlewheels.test&phone=0241234567&terms=1" --data-urlencode "password=$PASS" --data-urlencode "password_confirm=$PASS" >/dev/null
done
check "$(sql "SELECT COUNT(*) FROM iw_users WHERE email LIKE '%.e2e@idlewheels.test'")" "3" "three accounts registered and logged in"
$PHP database/make-admin.php admin.e2e@idlewheels.test >/dev/null
check "$(sql "SELECT role FROM iw_users WHERE email='admin.e2e@idlewheels.test'")" "admin" "make-admin.php promotes an account"
check "$(curl -s -o /dev/null -w '%{http_code}' -b "$T/renter" "$B/admin")" "403" "a renter can't open /admin"

echo "2. Host application"
tk=$(token host /host/apply)
post host /host/apply --data-urlencode "_token=$tk" -d "display_name=E2E Host&phone=0241234567&city=Labone, Accra&payout_method=MoMo 0241234567&agree=1" >/dev/null
HP=$(sql "SELECT h.id FROM iw_host_profiles h JOIN iw_users u ON u.id=h.user_id WHERE u.email='host.e2e@idlewheels.test'")
check "$(sql "SELECT status FROM iw_host_profiles WHERE id=$HP")" "pending" "application is pending"
check "$(curl -s -o /dev/null -w '%{http_code}' -b "$T/host" "$B/host/cars/new")" "302" "unapproved host can't add a car"
tk=$(token admin "/admin?tab=hosts")
post admin "/admin/hosts/$HP" --data-urlencode "_token=$tk" -d "status=approved" >/dev/null
check "$(sql "SELECT status FROM iw_host_profiles WHERE id=$HP")" "approved" "admin approves the host"

echo "3. Host adds a car"
tk=$(token host /host/cars/new)
CAT=$(sql "SELECT id FROM iw_categories WHERE slug='sedan'")
post host /host/cars -F "_token=$tk" -F make=Honda -F model=E2E -F year=2018 -F category_id=$CAT -F seats=5 -F transmission=Automatic -F fuel=Petrol \
  -F "location=Labone, Accra" -F price_per_day=40 -F min_days=1 -F max_days=10 -F requires_approval=1 -F "description=A test car added by the end-to-end check." \
  -F "features[]=Air conditioning" -F "photos[]=@$PHOTO;type=image/png" >/dev/null
CAR=$(sql "SELECT id FROM iw_cars WHERE model='E2E'")
check "$(sql "SELECT status FROM iw_cars WHERE id=$CAR")" "pending" "car submitted for review"
check "$(sql "SELECT COUNT(*) FROM iw_car_photos WHERE car_id=$CAR AND path LIKE 'uploads/cars/%'")" "1" "photo uploaded"
SLUG=$(sql "SELECT slug FROM iw_cars WHERE id=$CAR")
check "$(curl -s -o /dev/null -w '%{http_code}' "$B/cars/$SLUG")" "404" "car isn't public before approval"
tk=$(token admin "/admin?tab=cars")
post admin "/admin/cars/$CAR" --data-urlencode "_token=$tk" -d "status=approved" >/dev/null
check "$(curl -s -o /dev/null -w '%{http_code}' "$B/cars/$SLUG")" "200" "car is live after approval"

echo "4. Renter books"
FROM=$(date -d "+3 days" +%F); TO=$(date -d "+6 days" +%F)
check "$(curl -s "$B/cars?from=$FROM&to=$TO" | grep -c "/cars/$SLUG")" "1" "car appears in a date search"
tk=$(token renter "/cars/$SLUG/book?from=$FROM&to=$TO&addons%5B3%5D=3")
post renter "/cars/$SLUG/book" --data-urlencode "_token=$tk" -d "from=$FROM&to=$TO&addons%5B3%5D=3&phone=0201112222&agree=1" >/dev/null
BK=$(sql "SELECT id FROM iw_bookings WHERE car_id=$CAR ORDER BY id DESC LIMIT 1")
check "$(sql "SELECT CONCAT(status,'|',days,'|',rental_amount,'|',addons_amount,'|',service_fee,'|',commission,'|',host_earnings,'|',total) FROM iw_bookings WHERE id=$BK")" \
  "pending_payment|3|120.00|60.00|12.60|27.00|153.00|192.60" "booking priced: rental + extras + 7% fee, 15% commission"
check "$(curl -s "$B/cars?from=$FROM&to=$TO" | grep -c "/cars/$SLUG")" "0" "held dates hide the car from that search"
echo "   (payment step simulated: Paystack keys are not configured locally)"
sql "UPDATE iw_bookings SET status='awaiting_host', payment_status='paid' WHERE id=$BK"
REF=$(sql "SELECT ref FROM iw_bookings WHERE id=$BK")

echo "5. Host answers"
check "$(curl -s -b "$T/host" "$B/host?tab=requests" | grep -q "/host/bookings/$REF/accept" && echo yes)" "yes" "request shows in the host dashboard with Accept/Decline"
tk=$(token host "/host?tab=requests")
post host "/host/bookings/$REF/accept" --data-urlencode "_token=$tk" -d "note=See you at 9am" >/dev/null
check "$(sql "SELECT status FROM iw_bookings WHERE id=$BK")" "confirmed" "host accepts the request"
check "$(curl -s -o /dev/null -w '%{http_code}' -b "$T/renter" "$B/bookings/$REF")" "200" "renter sees the booking"
check "$(curl -s -o /dev/null -w '%{http_code}' -b "$T/admin" "$B/bookings/$REF")" "200" "admin sees the booking"

echo "6. Review after the rental"
sql "UPDATE iw_bookings SET start_date=DATE_SUB(CURDATE(), INTERVAL 5 DAY), end_date=DATE_SUB(CURDATE(), INTERVAL 2 DAY) WHERE id=$BK"
tk=$(token renter "/bookings/$REF")
post renter "/bookings/$REF/review" --data-urlencode "_token=$tk" -d "rating=5&comment=Clean car and an easy hand-over." >/dev/null
check "$(sql "SELECT rating FROM iw_reviews WHERE booking_id=$BK")" "5" "renter leaves a review"
check "$(curl -s "$B/cars/$SLUG" | grep -c 'Clean car and an easy hand-over')" "1" "review shows on the car page"

echo "7. Featured listing (admin promotion) and revenue"
tk=$(token admin "/admin?tab=cars&status=approved")
post admin "/admin/cars/$CAR/feature" --data-urlencode "_token=$tk" -d "action=feature&days=7" >/dev/null
check "$(curl -s "$B/" | grep -c "/cars/$SLUG")" "1" "featured car shows on the home page"
# expected = commission + fee of every paid booking in the last 90 days (includes any real bookings on this machine)
REV=$(sql "SELECT FORMAT(COALESCE(SUM(commission + service_fee), 0), 2) FROM iw_bookings WHERE payment_status='paid' AND status IN ('confirmed','completed','awaiting_host') AND created_at >= CURDATE() - INTERVAL 90 DAY")
check "$(curl -s -b "$T/admin" "$B/admin?tab=revenue" | grep -c "GHS $REV")" "1" "revenue: commission + fee totals (GHS $REV, incl. the test booking's 39.60)"

echo "8. Every admin tab renders"
for tab in overview hosts cars bookings users revenue addons categories reviews messages settings; do
  code=$(curl -s -o "$T/page" -w '%{http_code}' -b "$T/admin" "$B/admin?tab=$tab")
  bad=$(grep -c -i -E "Warning|Notice|Fatal error|Uncaught" "$T/page")
  check "$code/$bad" "200/0" "admin tab $tab"
done
for p in "/account" "/account?tab=history" "/account?tab=saved" "/account?tab=profile" "/host" "/host?tab=listings" "/host?tab=earnings"; do
  jar=renter; [[ $p == /host* ]] && jar=host
  code=$(curl -s -o "$T/page" -w '%{http_code}' -b "$T/$jar" "$B$p")
  bad=$(grep -c -i -E "Warning|Notice|Fatal error|Uncaught" "$T/page")
  check "$code/$bad" "200/0" "$p"
done

echo "9. Security"
curl -s -o /dev/null -b "$T/renter" -c "$T/renter" -d "name=Changed+Without+Token" "$B/account/profile"
check "$(sql "SELECT name FROM iw_users WHERE email='renter.e2e@idlewheels.test'")" "E2E Renter" "a POST without a CSRF token changes nothing"
check "$(curl -s -o /dev/null -w '%{http_code}' -X POST -d '{}' "$B/payment/webhook")" "401" "unsigned Paystack webhook is refused"

cleanup
rm -rf "$T"
echo
echo "$ok passed, $fail failed"
[ "$fail" -eq 0 ]
