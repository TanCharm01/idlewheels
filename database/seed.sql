-- demo data

SET NAMES utf8mb4;

INSERT INTO iw_settings (name, value) VALUES
  ('commission_percent', '15'),
  ('service_fee_percent', '7'),
  ('service_fee_name', 'IdleWheels service fee'),
  ('featured_price', '50'),
  ('featured_days', '7'),
  ('currency', 'GHS');

INSERT INTO iw_categories (name, slug, blurb, image, sort_order) VALUES
  ('Hatchback', 'hatchback', 'Easy to park, light on fuel', 'https://static.wixstatic.com/media/3e96a3_539f2fc62b064e15820e812a4e7f88a5~mv2.png', 10),
  ('Sedan', 'sedan', 'Comfortable for longer drives', 'uploads/categories/sedan.jpg', 20),
  ('SUV', 'suv', 'Room for family, luggage and rougher roads', 'https://static.wixstatic.com/media/3e96a3_508160fbf86c40299279fa818722a323~mv2.png', 30),
  ('Pickup', 'pickup', 'For moves, hauls and work', 'https://static.wixstatic.com/media/3e96a3_d7a859ffad4340ee8c55b2e6a5b9c99f~mv2.png', 40);

-- demo hosts (can't log in)
INSERT INTO iw_users (id, name, email, phone, password_hash, role) VALUES
  (1, 'Ama O.', 'ama.demo@idlewheels.test', NULL, '!', 'user'),
  (2, 'Kwame A.', 'kwame.demo@idlewheels.test', NULL, '!', 'user'),
  (3, 'Efua M.', 'efua.demo@idlewheels.test', NULL, '!', 'user'),
  (4, 'Kofi B.', 'kofi.demo@idlewheels.test', NULL, '!', 'user');
ALTER TABLE iw_users AUTO_INCREMENT = 100;

INSERT INTO iw_host_profiles (user_id, display_name, phone, city, about, payout_method, status) VALUES
  (1, 'Ama O.', '000 000 0001', 'East Legon, Accra', 'Demo host.', 'Demo', 'approved'),
  (2, 'Kwame A.', '000 000 0002', 'Osu, Accra', 'Demo host.', 'Demo', 'approved'),
  (3, 'Efua M.', '000 000 0003', 'Airport Residential, Accra', 'Demo host.', 'Demo', 'approved'),
  (4, 'Kofi B.', '000 000 0004', 'Tema', 'Demo host.', 'Demo', 'approved');

INSERT INTO iw_cars (id, host_id, category_id, slug, title, make, model, year, seats, transmission, fuel, location, price_per_day, min_days, max_days, requires_approval, tagline, description, features, status) VALUES
  (1, 1, 1, '2021-toyota-yaris', '2021 Toyota Yaris', 'Toyota', 'Yaris', 2021, 5, 'Automatic', 'Petrol', 'East Legon, Accra', 35.00, 1, 8, 0,
   'Frugal city runabout, owner-hosted',
   'A tidy, fuel-sipping hatchback that usually sits in its owner''s driveway on weekdays. Automatic, five seats, air-con, Bluetooth audio. Perfect for errands, airport runs and short trips. Pick-up and return arranged with the owner.',
   'Air conditioning,Bluetooth audio,Reversing sensors,USB charging', 'approved'),
  (2, 2, 1, '2020-volkswagen-polo', '2020 Volkswagen Polo', 'Volkswagen', 'Polo', 2020, 5, 'Manual', 'Petrol', 'Osu, Accra', 70.00, 1, 5, 0,
   'Nimble city hatchback',
   'A nimble manual hatchback available while its owner works from home. Ideal for a supermarket run, a viewing across town or a quick visit. Four doors, roomy boot, phone holder and USB charging.',
   'Air conditioning,Phone holder,USB charging', 'approved'),
  (3, 3, 3, '2022-toyota-rav4', '2022 Toyota RAV4', 'Toyota', 'RAV4', 2022, 5, 'Automatic', 'Hybrid', 'Airport Residential, Accra', 65.00, 2, 8, 1,
   'Family SUV for weekends away',
   'Spacious and comfortable SUV that its owner only drives a few days a month. All-wheel drive, roof rails, large boot and a reversing camera. Great for family weekends and long-distance trips. Each request is reviewed by the owner before it''s confirmed.',
   'All-wheel drive,Roof rails,Reversing camera,Apple CarPlay', 'approved'),
  (4, 4, 4, '2019-ford-ranger-double-cab', '2019 Ford Ranger Double Cab', 'Ford', 'Ranger', 2019, 5, 'Manual', 'Diesel', 'Tema', 70.00, 1, 5, 0,
   'Workhorse pickup for moves and hauls',
   'A sturdy double-cab pickup with a lined load bed and tow bar, rented out by its owner between jobs. Ideal for moving house, garden waste runs or carrying gear. Diesel, four-wheel drive, seats five.',
   'Four-wheel drive,Tow bar,Lined load bed', 'approved');

INSERT INTO iw_car_photos (car_id, path, sort_order) VALUES
  (1, 'https://static.wixstatic.com/media/3e96a3_539f2fc62b064e15820e812a4e7f88a5~mv2.png', 0),
  (2, 'https://static.wixstatic.com/media/3e96a3_f9d91f83b2f640489d8065aa37fd493d~mv2.png', 0),
  (3, 'https://static.wixstatic.com/media/3e96a3_508160fbf86c40299279fa818722a323~mv2.png', 0),
  (4, 'https://static.wixstatic.com/media/3e96a3_d7a859ffad4340ee8c55b2e6a5b9c99f~mv2.png', 0);

INSERT INTO iw_addons (name, description, price, per_day, max_quantity, sort_order) VALUES
  ('Driver hire', 'A professional driver for the day', 150.00, 1, 14, 10),
  ('Delivery to your address', 'The car is brought to you', 50.00, 0, 1, 20),
  ('Child seat', 'Fitted before pick-up', 20.00, 1, 14, 30),
  ('GPS navigator', 'Portable sat-nav', 15.00, 1, 14, 40),
  ('Fuel package', 'Return the car without refuelling', 400.00, 0, 1, 50);

INSERT INTO iw_faqs (category, question, answer, sort_order) VALUES
  ('Renting a car', 'What do I need to rent a car?', 'An IdleWheels account, a full driving licence you have held for at least two years, and a phone number the host can reach you on. You must be at least 21 years old.', 10),
  ('Renting a car', 'How do I book?', 'Find a car with the search or filters, pick your pick-up and return dates, add any extras, check the total and pay securely with card or mobile money.', 20),
  ('Renting a car', 'Why does a car say "Request to book"?', 'That host reviews each booking before confirming it. You pay when you request; if the host declines, your payment is refunded.', 30),
  ('Renting a car', 'Where do I collect the car?', 'At the location shown on the car''s page. The host contacts you before your booking to agree the exact hand-over point and time.', 40),
  ('Payments and fees', 'How do I pay?', 'Online with card or mobile money through Paystack. The total you see before paying includes the car, any extras and the IdleWheels service fee.', 10),
  ('Payments and fees', 'What is the service fee?', 'A small percentage added to each booking to cover payment processing and running the platform. It is shown separately before you pay.', 20),
  ('Payments and fees', 'Can I cancel a booking?', 'Yes. The refund depends on how far ahead you cancel; the windows are set out in the Rental Policy.', 30),
  ('Hosting your car', 'Who can list a car?', 'Anyone who owns a roadworthy, registered and insured car. Create an account, apply to host, and add your car with photos, specs and a daily price.', 10),
  ('Hosting your car', 'What does IdleWheels charge hosts?', 'Listing is free. IdleWheels keeps a commission on each completed rental, shown in your dashboard. You can also pay to feature a car at the top of search.', 20),
  ('Hosting your car', 'Do I have to accept every booking?', 'No. If your car requires approval, you accept or decline each request from your host dashboard.', 30),
  ('Hosting your car', 'How do I get paid?', 'Your earnings after commission are shown in your dashboard and paid out by the IdleWheels team to the mobile money number or bank account on your host profile.', 40);
