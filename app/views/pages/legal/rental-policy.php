<?php
use App\Models\Setting;

$feePct = rtrim(rtrim(number_format(Setting::num('service_fee_percent', 7), 2), '0'), '.');
ob_start();
?>
<section id="booking-requests">
  <h2>1. Booking and payment</h2>
  <p>
    When you choose a car and your pick-up and return dates, you see the full price in Ghanaian cedi (GHS) before you pay: the daily rate for each day,
    any extras you add, and the IdleWheels service fee (currently <?= e($feePct) ?>%). You pay the whole amount online, by card or mobile money through
    Paystack, when you book.
  </p>
  <ul>
    <li><strong>Instant booking:</strong> on most cars your booking is confirmed as soon as your payment goes through.</li>
    <li><strong>Request to book:</strong> some hosts approve each booking first. Your payment holds the car while the host decides, and hosts are asked to
      answer within 24 hours. If the host declines, or hasn't accepted by the pick-up date, the booking is cancelled and you get a full refund, including the
      service fee.</li>
  </ul>
  <p>You can see the status of every booking under <a href="<?= url('/account') ?>">My account</a>.</p>
</section>

<section id="pick-up-return">
  <h2>2. Pick-up and return</h2>
  <ul>
    <li>Your rental runs from the pick-up date to the return date shown in your booking. The host will contact you to agree the exact time and place.</li>
    <li>Bring your valid driving licence. The host may check it before handing over the keys and can refuse the hand-over if you don't have it.</li>
    <li>At pick-up, walk around the car with the host and take photos of the outside, inside, fuel level and odometer. Do the same when you return it.</li>
    <li>Everything has already been paid online, so there is nothing to pay the host at hand-over.</li>
    <li>Return the car reasonably clean, with all keys, documents and any extras (such as a child seat or GPS) you were given.</li>
  </ul>
</section>

<section id="late-returns">
  <h2>3. Late returns</h2>
  <p>
    If you are running late, tell the host as soon as possible. There is a one-hour grace period. After that, each extra day or part of a day is charged at the
    car's daily rate, payable to the host. If the car has not been returned 24 hours after the agreed time and we cannot reach you, the host and IdleWheels may
    report it to the police.
  </p>
</section>

<section id="fuel">
  <h2>4. Fuel</h2>
  <p>
    Return the car with the same amount of fuel it had at pick-up. If it comes back with less, you pay the host for the missing fuel at the pump price plus a
    GHS 50 refuelling charge. If you added the <strong>Fuel package</strong> extra when booking, you can return the car without refuelling. Only use the fuel
    type shown on the listing; damage caused by the wrong fuel is the renter's responsibility.
  </p>
</section>

<section id="mileage">
  <h2>5. Mileage</h2>
  <p>
    Each booking includes 250 km per rental day. Distance beyond that is charged at GHS 1 per km, payable to the host, unless the listing says otherwise.
    Check the odometer reading together at pick-up and return.
  </p>
</section>

<section id="renter-cancellations">
  <h2>6. Cancellations by renters</h2>
  <p>
    You can cancel a booking yourself from <a href="<?= url('/account') ?>">My account</a> up to the day before pick-up. After that, contact us through the
    <a href="<?= url('/contact') ?>">contact page</a>. How much you get back depends on how early you cancel:
  </p>
  <table>
    <thead>
      <tr>
        <th scope="col">When you cancel</th>
        <th scope="col">Refund of the rental and extras</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>More than 48 hours before pick-up</td>
        <td>100%</td>
      </tr>
      <tr>
        <td>24 to 48 hours before pick-up</td>
        <td>50%</td>
      </tr>
      <tr>
        <td>Less than 24 hours before pick-up, or you don't show up</td>
        <td>No refund</td>
      </tr>
    </tbody>
  </table>
  <p>
    The IdleWheels service fee is not refunded when you cancel, because it covers the payment and booking already processed. It is refunded in full when a
    host declines or cancels.
  </p>
</section>

<section id="owner-cancellations">
  <h2>7. Cancellations by hosts</h2>
  <p>
    Hosts should only cancel when they genuinely cannot provide the car. If a host declines a request or cancels a confirmed booking, you get a full refund of
    everything you paid, including the service fee, and we will try to help you find another car. Hosts who cancel often or at short notice may have their
    listings hidden or their hosting account suspended.
  </p>
</section>

<section id="refunds">
  <h2>8. Refunds</h2>
  <p>
    Refunds go back to the card or mobile money account you paid with, through Paystack. We start the refund within 5 working days of the cancellation. After
    that, mobile money refunds usually arrive within a few days, and card refunds can take up to 10 working days depending on your bank. Your booking shows
    "Refund due" until the refund has been sent, and "Refunded" after.
  </p>
</section>

<section id="deposits">
  <h2>9. Damage and extra charges</h2>
  <p>
    IdleWheels does not take a damage deposit. If there is damage, missing fuel, extra mileage, late return or unpaid traffic fines during your rental, the
    host can claim the cost from you, supported by photos and receipts. Charges for damage are handled as set out in
    <a href="<?= url('/insurance-safety') ?>">Insurance &amp; Safety</a> and the <a href="<?= url('/terms') ?>">Terms of Service</a>.
  </p>
</section>

<section id="disputes">
  <h2>10. Disputes</h2>
  <p>
    If you and the host cannot agree on a charge, damage claim or refund, send us a message through the <a href="<?= url('/contact') ?>">contact page</a>
    within 7 days of the end of the booking. Include your booking reference, photos and any receipts. We will look at the evidence from both sides and reply with
    a decision within 5 working days. Either side can still take the matter to the courts of Ghana.
  </p>
</section>
<?php
$body = ob_get_clean();
$sections = [
    ['id' => 'booking-requests', 'title' => 'Booking and payment'],
    ['id' => 'pick-up-return', 'title' => 'Pick-up and return'],
    ['id' => 'late-returns', 'title' => 'Late returns'],
    ['id' => 'fuel', 'title' => 'Fuel'],
    ['id' => 'mileage', 'title' => 'Mileage'],
    ['id' => 'renter-cancellations', 'title' => 'Cancellations by renters'],
    ['id' => 'owner-cancellations', 'title' => 'Cancellations by hosts'],
    ['id' => 'refunds', 'title' => 'Refunds'],
    ['id' => 'deposits', 'title' => 'Damage and extra charges'],
    ['id' => 'disputes', 'title' => 'Disputes'],
];
$legalTitle = 'Rental Policy, Cancellation & Refunds';
$legalEyebrow = 'Renting on IdleWheels';
$legalIntro = 'How bookings, pick-up and return, cancellations and refunds work for renters and hosts';
$legalDraft = false;
$legalUpdated = date('Y-m-d');
echo App\Core\View::partial('pages/legal/_legal', compact('legalTitle', 'legalIntro', 'legalEyebrow', 'sections', 'body', 'legalDraft', 'legalUpdated'));
