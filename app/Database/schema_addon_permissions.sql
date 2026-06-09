-- SalonCMS — permissions add-on (idempotent)
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS permissions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE,
  label VARCHAR(120) NOT NULL,
  module VARCHAR(40) NOT NULL,
  created_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS role_permissions (
  role_id INT UNSIGNED NOT NULL,
  permission_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  KEY idx_rp_perm (permission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default permission set (4 verbs × 7 modules)
INSERT IGNORE INTO permissions (name, label, module, created_at) VALUES
  ('customers.view',   'View customers',   'Customers',    NOW()),
  ('customers.create', 'Create customers', 'Customers',    NOW()),
  ('customers.edit',   'Edit customers',   'Customers',    NOW()),
  ('customers.delete', 'Delete customers', 'Customers',    NOW()),

  ('appointments.view',   'View appointments',   'Appointments', NOW()),
  ('appointments.create', 'Create appointments', 'Appointments', NOW()),
  ('appointments.edit',   'Edit appointments',   'Appointments', NOW()),
  ('appointments.delete', 'Delete appointments', 'Appointments', NOW()),

  ('services.view',   'View services',   'Services', NOW()),
  ('services.create', 'Create services', 'Services', NOW()),
  ('services.edit',   'Edit services',   'Services', NOW()),
  ('services.delete', 'Delete services', 'Services', NOW()),

  ('staff.view',   'View staff',   'Staff', NOW()),
  ('staff.create', 'Create staff', 'Staff', NOW()),
  ('staff.edit',   'Edit staff',   'Staff', NOW()),
  ('staff.delete', 'Delete staff', 'Staff', NOW()),

  ('invoices.view',   'View invoices',   'Billing', NOW()),
  ('invoices.create', 'Create invoices', 'Billing', NOW()),
  ('invoices.edit',   'Edit invoices',   'Billing', NOW()),
  ('invoices.delete', 'Delete invoices', 'Billing', NOW()),

  ('payments.view',   'View payments',   'Billing', NOW()),
  ('payments.record', 'Record payments', 'Billing', NOW()),

  ('settings.view', 'View settings', 'System', NOW()),
  ('settings.edit', 'Edit settings', 'System', NOW()),
  ('users.manage',  'Manage users',  'System', NOW()),
  ('roles.manage',  'Manage roles',  'System', NOW());

-- Grant ALL permissions to super_admin (role_id = 1, assuming seeded order)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
  SELECT (SELECT id FROM roles WHERE name = 'super_admin'), id FROM permissions;

-- Reasonable defaults for other roles
-- Branch Manager: everything except super-system stuff
INSERT IGNORE INTO role_permissions (role_id, permission_id)
  SELECT (SELECT id FROM roles WHERE name = 'branch_manager'), p.id
    FROM permissions p
    WHERE p.name NOT IN ('users.manage','roles.manage');

-- Receptionist: bookings + billing + customers (view+create+edit, no delete)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
  SELECT (SELECT id FROM roles WHERE name = 'receptionist'), p.id
    FROM permissions p
    WHERE p.name IN (
      'customers.view','customers.create','customers.edit',
      'appointments.view','appointments.create','appointments.edit',
      'services.view',
      'invoices.view','invoices.create','invoices.edit',
      'payments.view','payments.record'
    );

-- Stylist: read-only for own appointments
INSERT IGNORE INTO role_permissions (role_id, permission_id)
  SELECT (SELECT id FROM roles WHERE name = 'stylist'), p.id
    FROM permissions p
    WHERE p.name IN ('appointments.view','customers.view','services.view');

-- Accountant: invoices + payments + view
INSERT IGNORE INTO role_permissions (role_id, permission_id)
  SELECT (SELECT id FROM roles WHERE name = 'accountant'), p.id
    FROM permissions p
    WHERE p.name IN (
      'invoices.view','invoices.create','invoices.edit',
      'payments.view','payments.record',
      'customers.view','appointments.view'
    );
