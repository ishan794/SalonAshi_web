-- SalonCMS — Phase 1 schema
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS invoice_items;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS appointment_services;
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS staff_services;
DROP TABLE IF EXISTS staff;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS service_categories;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS branches;
DROP TABLE IF EXISTS user_roles;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS settings;

CREATE TABLE roles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE,
  label VARCHAR(100) NOT NULL,
  created_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role_id INT UNSIGNED NOT NULL,
  branch_id BIGINT UNSIGNED NULL,
  phone VARCHAR(30) NULL,
  status ENUM('active','disabled') DEFAULT 'active',
  last_login_at DATETIME NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  KEY idx_users_role (role_id),
  KEY idx_users_branch (branch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE branches (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  address TEXT NULL,
  phone VARCHAR(30) NULL,
  email VARCHAR(150) NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at DATETIME NULL,
  updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE customers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  branch_id BIGINT UNSIGNED NULL,
  full_name VARCHAR(150) NOT NULL,
  mobile VARCHAR(30) NOT NULL,
  email VARCHAR(150) NULL,
  gender ENUM('male','female','other') NULL,
  birthday DATE NULL,
  address TEXT NULL,
  preferred_stylist_id BIGINT UNSIGNED NULL,
  notes TEXT NULL,
  membership ENUM('none','silver','gold','platinum') DEFAULT 'none',
  loyalty_points INT DEFAULT 0,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  KEY idx_customers_mobile (mobile),
  KEY idx_customers_branch (branch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE service_categories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at DATETIME NULL,
  updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE services (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id BIGINT UNSIGNED NULL,
  name VARCHAR(150) NOT NULL,
  description TEXT NULL,
  duration_min INT NOT NULL DEFAULT 30,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  tax_pct DECIMAL(5,2) DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  KEY idx_services_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE staff (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  branch_id BIGINT UNSIGNED NULL,
  user_id BIGINT UNSIGNED NULL,
  full_name VARCHAR(150) NOT NULL,
  role VARCHAR(80) NULL,
  mobile VARCHAR(30) NULL,
  email VARCHAR(150) NULL,
  commission_pct DECIMAL(5,2) DEFAULT 0,
  working_hours VARCHAR(120) NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  KEY idx_staff_branch (branch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE staff_services (
  staff_id BIGINT UNSIGNED NOT NULL,
  service_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (staff_id, service_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE appointments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(20) NOT NULL UNIQUE,
  customer_id BIGINT UNSIGNED NOT NULL,
  staff_id BIGINT UNSIGNED NOT NULL,
  branch_id BIGINT UNSIGNED NULL,
  start_at DATETIME NOT NULL,
  end_at DATETIME NOT NULL,
  status ENUM('pending','confirmed','checked_in','in_progress','completed','cancelled','no_show') DEFAULT 'pending',
  subtotal DECIMAL(10,2) DEFAULT 0,
  notes TEXT NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  KEY idx_appt_customer (customer_id),
  KEY idx_appt_staff (staff_id),
  KEY idx_appt_start (start_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE appointment_services (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  appointment_id BIGINT UNSIGNED NOT NULL,
  service_id BIGINT UNSIGNED NOT NULL,
  service_name VARCHAR(150) NOT NULL,
  duration_min INT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  KEY idx_as_appt (appointment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE invoices (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_no VARCHAR(30) NOT NULL UNIQUE,
  appointment_id BIGINT UNSIGNED NULL,
  customer_id BIGINT UNSIGNED NOT NULL,
  staff_id BIGINT UNSIGNED NULL,
  branch_id BIGINT UNSIGNED NULL,
  subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
  discount DECIMAL(10,2) NOT NULL DEFAULT 0,
  tax DECIMAL(10,2) NOT NULL DEFAULT 0,
  total DECIMAL(10,2) NOT NULL DEFAULT 0,
  paid DECIMAL(10,2) NOT NULL DEFAULT 0,
  balance DECIMAL(10,2) NOT NULL DEFAULT 0,
  status ENUM('draft','unpaid','partial','paid','refunded','cancelled') DEFAULT 'unpaid',
  notes TEXT NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  KEY idx_inv_customer (customer_id),
  KEY idx_inv_appt (appointment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE invoice_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_id BIGINT UNSIGNED NOT NULL,
  item_type ENUM('service','product') DEFAULT 'service',
  ref_id BIGINT UNSIGNED NULL,
  name VARCHAR(180) NOT NULL,
  qty DECIMAL(10,2) NOT NULL DEFAULT 1,
  unit_price DECIMAL(10,2) NOT NULL DEFAULT 0,
  tax_pct DECIMAL(5,2) DEFAULT 0,
  line_total DECIMAL(10,2) NOT NULL DEFAULT 0,
  KEY idx_ii_invoice (invoice_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE payments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_id BIGINT UNSIGNED NOT NULL,
  method ENUM('cash','card','bank_transfer','mobile_wallet','online') NOT NULL DEFAULT 'cash',
  amount DECIMAL(10,2) NOT NULL,
  txn_ref VARCHAR(100) NULL,
  status ENUM('pending','success','failed','refunded') DEFAULT 'success',
  received_by BIGINT UNSIGNED NULL,
  paid_at DATETIME NULL,
  created_at DATETIME NULL,
  KEY idx_pay_invoice (invoice_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE settings (
  k VARCHAR(100) PRIMARY KEY,
  v TEXT NULL,
  updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS=1;

-- ── Seed data ──
INSERT INTO roles (name, label, created_at) VALUES
  ('super_admin','Super Admin', NOW()),
  ('branch_manager','Branch Manager', NOW()),
  ('receptionist','Receptionist', NOW()),
  ('stylist','Stylist / Staff', NOW()),
  ('accountant','Accountant', NOW()),
  ('customer','Customer', NOW());

INSERT INTO branches (name, address, phone, email, created_at) VALUES
  ('Main Branch','123 Main Street, Colombo','+94 11 234 5678','main@saloncms.local', NOW());

INSERT INTO settings (k, v, updated_at) VALUES
  ('salon_name','SalonCMS Demo', NOW()),
  ('salon_currency','LKR', NOW()),
  ('salon_tax_pct','0', NOW()),
  ('salon_invoice_prefix','INV-', NOW()),
  ('salon_appt_prefix','APT-', NOW());

INSERT INTO service_categories (name, sort_order, created_at) VALUES
  ('Haircut',1, NOW()),('Hair Coloring',2, NOW()),('Facial',3, NOW()),
  ('Makeup',4, NOW()),('Nail Care',5, NOW()),('Massage',6, NOW()),('Spa',7, NOW());

INSERT INTO services (category_id, name, duration_min, price, created_at) VALUES
  (1,'Men Haircut',30,800, NOW()),
  (1,'Women Haircut',45,1500, NOW()),
  (2,'Hair Color (Short)',90,4500, NOW()),
  (3,'Classic Facial',60,3000, NOW()),
  (4,'Bridal Makeup',180,25000, NOW()),
  (5,'Manicure',45,1800, NOW()),
  (5,'Pedicure',60,2200, NOW()),
  (6,'Head Massage',30,1500, NOW()),
  (7,'Aromatherapy Spa',90,7500, NOW());

INSERT INTO staff (branch_id, full_name, role, mobile, commission_pct, working_hours, created_at) VALUES
  (1,'Nimal Perera','Senior Stylist','+94 77 111 2222', 10, '09:00-18:00', NOW()),
  (1,'Sanduni Silva','Beautician','+94 77 333 4444', 12, '10:00-19:00', NOW()),
  (1,'Ravi Fernando','Barber','+94 77 555 6666', 8, '09:00-17:00', NOW());

INSERT INTO staff_services (staff_id, service_id) VALUES
  (1,1),(1,2),(1,3),
  (2,4),(2,5),(2,6),(2,7),
  (3,1);

INSERT INTO customers (branch_id, full_name, mobile, email, gender, birthday, membership, loyalty_points, created_at) VALUES
  (1,'Anjali Karunaratne','+94 71 200 1111','anjali@example.com','female','1995-03-15','gold',120, NOW()),
  (1,'Kasun Wijesinghe','+94 71 200 2222','kasun@example.com','male','1990-07-22','silver',45, NOW()),
  (1,'Mihiri Bandara','+94 71 200 3333','mihiri@example.com','female','1988-11-04','none',0, NOW());

-- Super admin user — password is "admin123" (bcrypt cost 10)
-- Generated via PHP: password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO users (name, email, password_hash, role_id, branch_id, status, created_at) VALUES
  ('Super Admin','admin@saloncms.local','$2y$10$ftY1DCvHb1MoLa6PwX5Ayekdz95pT/e860PNDFZVG/CobGxWF82ga',1,1,'active', NOW());
