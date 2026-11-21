<?php ob_start(); ?>
<section id="insurance">
  <h2>1. Insurance</h2>
  <p>
    <strong>IdleWheels does not provide insurance.</strong> Every car on IdleWheels must be covered by the host's own comprehensive motor insurance, and the
    policy must cover the car while it is rented to other drivers. Hosts must check this with their insurer before listing a car.
  </p>
  <p>
    Before driving, renters can ask the host to show the insurance certificate. If the car is damaged during your rental, you are responsible for the
    insurance excess and for any damage the insurance does not cover, as set out in the <a href="<?= url('/terms') ?>">Terms of Service</a> and the
    <a href="<?= url('/rental-policy') ?>">Rental Policy</a>.
  </p>
</section>

<section id="owner-requirements">
  <h2>2. Host requirements</h2>
  <p>To list a car on IdleWheels, hosts must have, and keep up to date:</p>
  <ul>
    <li>a valid registration with the Driver and Vehicle Licensing Authority (DVLA), in the host's name or with the registered owner's written permission;</li>
    <li>a valid roadworthy certificate;</li>
    <li>comprehensive motor insurance that covers rental to other drivers;</li>
    <li>a car in safe working order: tyres, brakes, lights, seat belts and wipers working, with a spare wheel, a jack, a warning triangle and a fire extinguisher.</li>
  </ul>
  <p>Hosts confirm all of this when they apply. We may hide a listing if a document runs out or we receive safety concerns about the car.</p>
</section>

<section id="renter-requirements">
  <h2>3. Renter requirements</h2>
  <ul>
    <li>Be at least 21 years old.</li>
    <li>Hold a full, valid driving licence for the type of car, and have held it for at least two years.</li>
    <li>Have an IdleWheels account with your real name and a phone number the host can reach you on.</li>
    <li>Be the only driver, unless the host agrees otherwise or you booked the Driver hire extra.</li>
  </ul>
</section>

<section id="before-you-drive">
  <h2>4. Before you drive</h2>
  <ul>
    <li>Check that the insurance certificate, roadworthy certificate and registration documents are valid and in the car.</li>
    <li>Walk around the car with the host and photograph any scratches or dents, the fuel level and the odometer.</li>
    <li>Check the tyres, lights, indicators, wipers, brakes and seat belts.</li>
    <li>Make sure the spare wheel, jack, warning triangle and fire extinguisher are in the car.</li>
    <li>Save the host's phone number so you can reach them during the rental.</li>
    <li>Adjust your seat and mirrors and get to know the controls before you set off.</li>
  </ul>
</section>

<section id="during-the-trip">
  <h2>5. During the rental</h2>
  <ul>
    <li>Follow the Road Traffic Act and all traffic rules. Everyone in the car must wear a seat belt.</li>
    <li>Never drive after drinking alcohol or taking drugs, and don't use your phone while driving.</li>
    <li>Don't let anyone else drive the car unless the host has agreed.</li>
    <li>Park in safe places and lock the car whenever you leave it.</li>
    <li>If a warning light comes on or something doesn't feel right, stop somewhere safe and call the host before going on.</li>
  </ul>
</section>

<section id="accident-breakdown">
  <h2>6. If you have an accident or breakdown</h2>
  <ol>
    <li>Stop somewhere safe, turn on the hazard lights and put the warning triangle behind the car.</li>
    <li>Check whether anyone is hurt. In an emergency call <strong>112</strong>, or police <strong>191</strong>, fire service <strong>192</strong> and ambulance <strong>193</strong>.</li>
    <li>Report any accident to the police and ask for a police report or reference number.</li>
    <li>Don't admit fault or agree to pay anyone at the scene.</li>
    <li>Take photos of the scene, the cars involved, the damage and the number plates, and swap names, phone numbers and insurance details with the other drivers.</li>
    <li>Call the host straight away, then tell IdleWheels through the <a href="<?= url('/contact') ?>">contact page</a> with your booking reference.</li>
    <li>If the car breaks down, don't try major repairs yourself. Agree with the host what to do next; the host arranges any towing or roadside help.</li>
  </ol>
</section>

<section id="reporting">
  <h2>7. Reporting problems</h2>
  <p>
    Tell us about any accident, damage, theft, unsafe car or behaviour that made you feel unsafe through the <a href="<?= url('/contact') ?>">contact page</a>
    as soon as you can, with your booking reference and any photos. In an emergency, always call the emergency services first. We look into every safety report
    and may hide a listing or block an account while we do. Disagreements about damage charges are handled under our
    <a href="<?= url('/rental-policy') ?>#disputes">disputes process</a>.
  </p>
</section>
<?php
$body = ob_get_clean();
$sections = [
    ['id' => 'insurance', 'title' => 'Insurance'],
    ['id' => 'owner-requirements', 'title' => 'Host requirements'],
    ['id' => 'renter-requirements', 'title' => 'Renter requirements'],
    ['id' => 'before-you-drive', 'title' => 'Before you drive'],
    ['id' => 'during-the-trip', 'title' => 'During the rental'],
    ['id' => 'accident-breakdown', 'title' => 'If you have an accident or breakdown'],
    ['id' => 'reporting', 'title' => 'Reporting problems'],
];
$legalTitle = 'Insurance & Safety';
$legalEyebrow = 'Trust and safety';
$legalIntro = 'Who covers what, what hosts and renters need, and what to do if something goes wrong on the road';
$legalDraft = false;
$legalUpdated = date('Y-m-d');
echo App\Core\View::partial('pages/legal/_legal', compact('legalTitle', 'legalIntro', 'legalEyebrow', 'sections', 'body', 'legalDraft', 'legalUpdated'));
