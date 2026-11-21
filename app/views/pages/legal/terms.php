<?php
use App\Models\Setting;

$pct = function (string $key, float $default): string {
    return rtrim(rtrim(number_format(Setting::num($key, $default), 2), '0'), '.');
};
$feePct = $pct('service_fee_percent', 7);
$commissionPct = $pct('commission_percent', 15);
$featuredPrice = money_short(Setting::num('featured_price', 50));
$featuredDays = (int) Setting::num('featured_days', 7);
ob_start();
?>
<section id="acceptance">
  <h2>1. Acceptance of these terms</h2>
  <p>
    These Terms of Service ("Terms") apply to your use of the IdleWheels website ("IdleWheels", "we", "us"). By creating an account, booking a car or listing
    a car, you agree to these Terms, our <a href="<?= url('/privacy') ?>">Privacy Policy</a>, our <a href="<?= url('/rental-policy') ?>">Rental Policy</a> and
    our <a href="<?= url('/insurance-safety') ?>">Insurance &amp; Safety</a> rules. If you do not agree, please do not use IdleWheels.
  </p>
</section>

<section id="marketplace">
  <h2>2. Our role as a marketplace</h2>
  <p>
    IdleWheels is an online marketplace in Ghana that connects people who own cars ("hosts") with people who want to rent them ("renters"). Each rental is an
    agreement between the host and the renter. IdleWheels does not own the cars listed and is not a car rental company, transport operator or insurer.
  </p>
  <p>
    We review hosts and their cars before listings go live, take payments for bookings, and help resolve disputes. We do not guarantee the condition or
    availability of any car, or the behaviour of any host or renter.
  </p>
</section>

<section id="accounts">
  <h2>3. Accounts</h2>
  <ul>
    <li>You must be at least 21 years old to create an account, rent a car or host a car.</li>
    <li>Your account details (name, email address and phone number) must be accurate and kept up to date.</li>
    <li>Keep your password private. You are responsible for everything done through your account. If you think someone else has used it, change your password and tell us through the <a href="<?= url('/contact') ?>">contact page</a>.</li>
    <li>Each person may have one account, and accounts can't be transferred.</li>
  </ul>
</section>

<section id="renter-obligations">
  <h2>4. Renter obligations</h2>
  <p>As a renter you agree to:</p>
  <ul>
    <li>hold a full, valid driving licence for at least two years, and carry it whenever you drive;</li>
    <li>be the only driver, unless the host agrees otherwise or you booked the Driver hire extra;</li>
    <li>use the car carefully, lawfully and only for the dates booked;</li>
    <li>return the car on time, in the same condition you received it apart from normal wear and tear;</li>
    <li>follow the fuel, mileage and late-return rules in the <a href="<?= url('/rental-policy') ?>">Rental Policy</a>;</li>
    <li>pay any traffic fines, tolls or parking charges you get during the rental;</li>
    <li>report any accident, damage, breakdown or theft to the host and to IdleWheels straight away, and to the police where the law requires.</li>
  </ul>
</section>

<section id="owner-obligations">
  <h2>5. Host obligations</h2>
  <p>As a host you agree to:</p>
  <ul>
    <li>be the registered owner of the car, or have the registered owner's written permission to list it;</li>
    <li>keep the car registered with the DVLA, roadworthy and insured for rental to other drivers, as set out in <a href="<?= url('/insurance-safety') ?>">Insurance &amp; Safety</a>;</li>
    <li>describe the car honestly, with real photos, the correct specs, location and price, and any known faults;</li>
    <li>answer booking requests promptly from your host dashboard and honour the bookings you accept;</li>
    <li>hand the car over clean, safe and with the documents the law requires;</li>
    <li>treat all renters fairly and not discriminate against anyone;</li>
    <li>declare and pay any tax due on the money you earn through IdleWheels.</li>
  </ul>
</section>

<section id="bookings-payment">
  <h2>6. Bookings, fees and payment</h2>
  <p>
    All prices are in Ghanaian cedi (GHS). Before you pay, you see the full total: the car's daily rate for each day, any extras you choose, and the IdleWheels
    service fee. Renters pay the full amount online when they book, by card or mobile money through Paystack, under Paystack's own terms. Some cars are
    confirmed instantly; others are confirmed once the host accepts the request.
  </p>
  <p>IdleWheels earns money in the following ways, all shown before you pay or list:</p>
  <ul>
    <li><strong>Service fee:</strong> renters pay <?= e($feePct) ?>% of the rental and extras on top of the price.</li>
    <li><strong>Booking commission:</strong> IdleWheels keeps <?= e($commissionPct) ?>% of the rental and extras from each booking. Hosts receive the rest.</li>
    <li><strong>Featured listings:</strong> hosts can pay <?= e($featuredPrice) ?> to have a car shown first in search and on the home page for <?= $featuredDays ?> days. Featured listing payments are not refundable.</li>
    <li><strong>Extras:</strong> renters can add extras such as driver hire, delivery, a child seat, GPS or a fuel package. Extras are charged with the booking.</li>
  </ul>
  <p>
    Host earnings (the rental and extras, minus commission) are paid by IdleWheels to the payout details on the host's profile within 7 days after a rental
    ends. We may change our fees in the future; any change applies only to bookings made after it.
  </p>
</section>

<section id="cancellations">
  <h2>7. Cancellations and refunds</h2>
  <p>
    Cancellations by renters and hosts, and the refunds that apply, are set out in our
    <a href="<?= url('/rental-policy') ?>">Rental Policy, Cancellation &amp; Refunds</a>, which forms part of these Terms.
  </p>
</section>

<section id="damage-liability">
  <h2>8. Damage and liability</h2>
  <p>
    The renter is responsible for loss of or damage to the car from pick-up until it is returned, except where it is covered by the host's insurance or caused
    by the host. How insurance works, and what to do after an accident, is explained in <a href="<?= url('/insurance-safety') ?>">Insurance &amp; Safety</a>.
  </p>
  <p>
    As far as the law of Ghana allows, IdleWheels is not responsible for any loss, damage, injury or cost arising from a rental, from the condition of a car or
    from anything a host or renter does. If we are found responsible, the most we will pay is the amount IdleWheels earned from the booking concerned.
    Nothing in these Terms limits liability for death or personal injury caused by negligence, for fraud, or for anything else that cannot legally be limited.
  </p>
</section>

<section id="prohibited-use">
  <h2>9. Prohibited use</h2>
  <p>You must not, and must not allow anyone else to:</p>
  <ul>
    <li>drive a rented car after drinking alcohol or taking drugs, or without a valid licence;</li>
    <li>use a rented car for taxi or ride-hailing work, deliveries, driving lessons or racing;</li>
    <li>carry more passengers or weight than the car is built for, or carry illegal or dangerous goods;</li>
    <li>take the car outside Ghana without the host's written permission;</li>
    <li>rent the car out to someone else;</li>
    <li>arrange or pay for a booking outside IdleWheels to avoid our fees;</li>
    <li>post false, misleading or abusive content, or use IdleWheels for fraud.</li>
  </ul>
</section>

<section id="reviews">
  <h2>10. Reviews</h2>
  <p>
    After a rental ends, the renter can leave one review of the car. Reviews must be honest and based on the real rental, and must not include personal
    information, offensive language or anything unlawful. We may hide reviews that break these rules, but we never change what a review says or accept payment
    to remove one.
  </p>
</section>

<section id="termination">
  <h2>11. Suspension and closing accounts</h2>
  <p>
    You can ask us to close your account at any time through the <a href="<?= url('/contact') ?>">contact page</a>, once any open bookings are finished or
    cancelled.
  </p>
  <p>
    We may block an account, hide a listing or cancel a booking if we reasonably believe someone has broken these Terms, given false information, put others
    at risk or acted unlawfully. Anything owed before that, such as payment for damage, is still owed afterwards.
  </p>
</section>

<section id="governing-law">
  <h2>12. Governing law</h2>
  <p>
    These Terms are governed by the laws of the Republic of Ghana. Any dispute that can't be settled through our disputes process will be dealt with by the
    courts of Ghana. Nothing here affects your rights under Ghanaian consumer law.
  </p>
</section>

<section id="changes">
  <h2>13. Changes to these terms</h2>
  <p>
    We may update these Terms. The new version will be posted on this page with a new date at the top. Bookings already made stay under the Terms that applied
    when they were made.
  </p>
</section>

<section id="contact">
  <h2>14. Contact us</h2>
  <p>If you have a question about these Terms, send us a message through our <a href="<?= url('/contact') ?>">contact page</a>.</p>
</section>
<?php
$body = ob_get_clean();
$sections = [
    ['id' => 'acceptance', 'title' => 'Acceptance of these terms'],
    ['id' => 'marketplace', 'title' => 'Our role as a marketplace'],
    ['id' => 'accounts', 'title' => 'Accounts'],
    ['id' => 'renter-obligations', 'title' => 'Renter obligations'],
    ['id' => 'owner-obligations', 'title' => 'Host obligations'],
    ['id' => 'bookings-payment', 'title' => 'Bookings, fees and payment'],
    ['id' => 'cancellations', 'title' => 'Cancellations and refunds'],
    ['id' => 'damage-liability', 'title' => 'Damage and liability'],
    ['id' => 'prohibited-use', 'title' => 'Prohibited use'],
    ['id' => 'reviews', 'title' => 'Reviews'],
    ['id' => 'termination', 'title' => 'Suspension and closing accounts'],
    ['id' => 'governing-law', 'title' => 'Governing law'],
    ['id' => 'changes', 'title' => 'Changes to these terms'],
    ['id' => 'contact', 'title' => 'Contact us'],
];
$legalTitle = 'Terms of Service';
$legalEyebrow = 'Legal';
$legalIntro = 'The rules for using IdleWheels, whether you are renting a car or listing your own';
$legalDraft = false;
$legalUpdated = date('Y-m-d');
echo App\Core\View::partial('pages/legal/_legal', compact('legalTitle', 'legalIntro', 'legalEyebrow', 'sections', 'body', 'legalDraft', 'legalUpdated'));
