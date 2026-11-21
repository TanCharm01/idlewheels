-- IdleWheels tables

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS iw_saved_cars, iw_reviews, iw_booking_addons, iw_payments, iw_featured_purchases, iw_bookings, iw_car_photos, iw_cars, iw_addons, iw_categories, iw_host_profiles, iw_contact_messages, iw_faqs, iw_settings, iw_users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE iw_users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  phone VARCHAR(30) NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  status ENUM('active','blocked') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE iw_host_profiles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL UNIQUE,
  display_name VARCHAR(100) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  city VARCHAR(100) NOT NULL,
  about TEXT NULL,
  payout_method VARCHAR(150) NOT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  admin_note VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  CONSTRAINT fk_host_user FOREIGN KEY (user_id) REFERENCES iw_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE iw_categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL,
  slug VARCHAR(80) NOT NULL UNIQUE,
  blurb VARCHAR(150) NULL,
  image VARCHAR(500) NULL,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE iw_cars (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  host_id INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NULL,
  slug VARCHAR(140) NOT NULL UNIQUE,
  title VARCHAR(140) NOT NULL,
  make VARCHAR(60) NOT NULL,
  model VARCHAR(60) NOT NULL,
  year SMALLINT UNSIGNED NOT NULL,
  seats TINYINT UNSIGNED NOT NULL DEFAULT 5,
  transmission ENUM('Automatic','Manual') NOT NULL,
  fuel ENUM('Petrol','Diesel','Hybrid','Electric') NOT NULL,
  location VARCHAR(120) NOT NULL,
  price_per_day DECIMAL(10,2) NOT NULL,
  min_days TINYINT UNSIGNED NOT NULL DEFAULT 1,
  max_days TINYINT UNSIGNED NOT NULL DEFAULT 14,
  requires_approval TINYINT(1) NOT NULL DEFAULT 1,
  tagline VARCHAR(160) NULL,
  description TEXT NOT NULL,
  features VARCHAR(600) NULL,
  status ENUM('pending','approved','rejected','hidden') NOT NULL DEFAULT 'pending',
  admin_note VARCHAR(500) NULL,
  featured_until DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  CONSTRAINT fk_car_host FOREIGN KEY (host_id) REFERENCES iw_users(id) ON DELETE CASCADE,
  CONSTRAINT fk_car_category FOREIGN KEY (category_id) REFERENCES iw_categories(id) ON DELETE SET NULL,
  INDEX idx_car_status (status),
  INDEX idx_car_featured (featured_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE iw_car_photos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  car_id INT UNSIGNED NOT NULL,
  path VARCHAR(500) NOT NULL,
  sort_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
  CONSTRAINT fk_photo_car FOREIGN KEY (car_id) REFERENCES iw_cars(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- extras
CREATE TABLE iw_addons (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description VARCHAR(200) NULL,
  price DECIMAL(10,2) NOT NULL,
  per_day TINYINT(1) NOT NULL DEFAULT 0,
  max_quantity TINYINT UNSIGNED NOT NULL DEFAULT 1,
  active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- end_date is the return day
CREATE TABLE iw_bookings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ref VARCHAR(20) NOT NULL UNIQUE,
  car_id INT UNSIGNED NOT NULL,
  renter_id INT UNSIGNED NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  days SMALLINT UNSIGNED NOT NULL,
  price_per_day DECIMAL(10,2) NOT NULL,
  rental_amount DECIMAL(10,2) NOT NULL,
  addons_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  service_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
  service_fee_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
  commission DECIMAL(10,2) NOT NULL DEFAULT 0,
  commission_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
  host_earnings DECIMAL(10,2) NOT NULL DEFAULT 0,
  total DECIMAL(10,2) NOT NULL,
  status ENUM('pending_payment','awaiting_host','confirmed','declined','cancelled','completed') NOT NULL DEFAULT 'pending_payment',
  payment_status ENUM('unpaid','paid','refund_due','refunded') NOT NULL DEFAULT 'unpaid',
  renter_phone VARCHAR(30) NULL,
  renter_note VARCHAR(500) NULL,
  host_note VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  CONSTRAINT fk_booking_car FOREIGN KEY (car_id) REFERENCES iw_cars(id) ON DELETE CASCADE,
  CONSTRAINT fk_booking_renter FOREIGN KEY (renter_id) REFERENCES iw_users(id) ON DELETE CASCADE,
  INDEX idx_booking_car_dates (car_id, start_date, end_date),
  INDEX idx_booking_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE iw_booking_addons (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id INT UNSIGNED NOT NULL,
  addon_id INT UNSIGNED NULL,
  name VARCHAR(100) NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  quantity SMALLINT UNSIGNED NOT NULL,
  line_total DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_ba_booking FOREIGN KEY (booking_id) REFERENCES iw_bookings(id) ON DELETE CASCADE,
  CONSTRAINT fk_ba_addon FOREIGN KEY (addon_id) REFERENCES iw_addons(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- featured listings
CREATE TABLE iw_featured_purchases (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  car_id INT UNSIGNED NOT NULL,
  host_id INT UNSIGNED NOT NULL,
  days SMALLINT UNSIGNED NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  source ENUM('purchase','admin') NOT NULL DEFAULT 'purchase',
  status ENUM('pending','paid') NOT NULL DEFAULT 'pending',
  starts_at DATETIME NULL,
  ends_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_fp_car FOREIGN KEY (car_id) REFERENCES iw_cars(id) ON DELETE CASCADE,
  CONSTRAINT fk_fp_host FOREIGN KEY (host_id) REFERENCES iw_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- paystack transactions
CREATE TABLE iw_payments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reference VARCHAR(60) NOT NULL UNIQUE,
  purpose ENUM('booking','featured') NOT NULL,
  booking_id INT UNSIGNED NULL,
  featured_purchase_id INT UNSIGNED NULL,
  user_id INT UNSIGNED NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'GHS',
  status ENUM('initialized','success','failed','abandoned') NOT NULL DEFAULT 'initialized',
  channel VARCHAR(30) NULL,
  paid_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pay_booking FOREIGN KEY (booking_id) REFERENCES iw_bookings(id) ON DELETE SET NULL,
  CONSTRAINT fk_pay_feature FOREIGN KEY (featured_purchase_id) REFERENCES iw_featured_purchases(id) ON DELETE SET NULL,
  CONSTRAINT fk_pay_user FOREIGN KEY (user_id) REFERENCES iw_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE iw_reviews (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id INT UNSIGNED NOT NULL UNIQUE,
  car_id INT UNSIGNED NOT NULL,
  renter_id INT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  comment TEXT NOT NULL,
  hidden TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_review_booking FOREIGN KEY (booking_id) REFERENCES iw_bookings(id) ON DELETE CASCADE,
  CONSTRAINT fk_review_car FOREIGN KEY (car_id) REFERENCES iw_cars(id) ON DELETE CASCADE,
  CONSTRAINT fk_review_user FOREIGN KEY (renter_id) REFERENCES iw_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE iw_saved_cars (
  user_id INT UNSIGNED NOT NULL,
  car_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, car_id),
  CONSTRAINT fk_saved_user FOREIGN KEY (user_id) REFERENCES iw_users(id) ON DELETE CASCADE,
  CONSTRAINT fk_saved_car FOREIGN KEY (car_id) REFERENCES iw_cars(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE iw_settings (
  name VARCHAR(60) PRIMARY KEY,
  value VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE iw_faqs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category VARCHAR(80) NOT NULL,
  question VARCHAR(255) NOT NULL,
  answer TEXT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE iw_contact_messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(30) NULL,
  topic VARCHAR(40) NOT NULL,
  booking_ref VARCHAR(20) NULL,
  message TEXT NOT NULL,
  handled TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
