#!/bin/bash
# ============================================================
#  LSUC Website — VPS MySQL Database Setup Script
#  Run this ONCE on your live VPS as root:
#    chmod +x vps_db_setup.sh && sudo bash vps_db_setup.sh
# ============================================================

set -e  # Exit immediately on any error

# ── Configuration (must match config/db_config.php) ───────────────────────────
DB_NAME="website_db"
DB_USER="lsuc_db_user"
DB_PASS="LsucPay@2026!"   # Change this if you want a different password
                           # (also update config/db_config.php to match)
# ──────────────────────────────────────────────────────────────────────────────

echo ""
echo "============================================"
echo "  LSUC Website — MySQL Database Setup"
echo "============================================"
echo ""

# Check MySQL is running
if ! command -v mysql &> /dev/null; then
    echo "ERROR: mysql client not found. Install it first:"
    echo "  Ubuntu/Debian:  sudo apt install mysql-client"
    echo "  CentOS/RHEL:    sudo yum install mysql"
    exit 1
fi

echo "Step 1: Creating database '$DB_NAME'..."
mysql -u root -p <<EOF
-- Create database
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- Create dedicated DB user (safer than using root)
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
CREATE USER IF NOT EXISTS '${DB_USER}'@'127.0.0.1' IDENTIFIED BY '${DB_PASS}';

-- Grant only what's needed (no SUPER, no FILE, no GRANT)
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, INDEX, ALTER
  ON \`${DB_NAME}\`.*
  TO '${DB_USER}'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, INDEX, ALTER
  ON \`${DB_NAME}\`.*
  TO '${DB_USER}'@'127.0.0.1';

FLUSH PRIVILEGES;

-- Switch to the new DB
USE \`${DB_NAME}\`;

-- Create payments table
CREATE TABLE IF NOT EXISTS \`payments\` (
  \`id\`                    INT AUTO_INCREMENT PRIMARY KEY,
  \`transaction_reference\` VARCHAR(100)  NOT NULL UNIQUE,
  \`phone_number\`          VARCHAR(30)   NOT NULL DEFAULT '',
  \`amount\`                DECIMAL(10,2) NOT NULL,
  \`status\`                VARCHAR(30)   NOT NULL DEFAULT 'pending',
  \`category\`              VARCHAR(100)  NULL,
  \`description\`           VARCHAR(255)  NULL,
  \`source_id\`             VARCHAR(255)  NULL,
  \`swish_trans_link_id\`   VARCHAR(100)  NULL,
  \`swish_method\`          VARCHAR(20)   NULL,
  \`created_at\`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  \`updated_at\`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create payment_logs table
CREATE TABLE IF NOT EXISTS \`payment_logs\` (
  \`id\`                    INT AUTO_INCREMENT PRIMARY KEY,
  \`transaction_reference\` VARCHAR(100)  NULL,
  \`raw_response\`          TEXT          NULL,
  \`action_taken\`          VARCHAR(50)   NULL,
  \`created_at\`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verify
SELECT CONCAT('Tables in ${DB_NAME}: ', COUNT(*)) AS result
FROM information_schema.tables
WHERE table_schema = '${DB_NAME}';
EOF

echo ""
echo "Step 2: Testing connection as '$DB_USER'..."
mysql -u "$DB_USER" -p"$DB_PASS" -h 127.0.0.1 "$DB_NAME" -e "SELECT 'Connection OK' AS status;" 2>/dev/null && echo "  ✅ Connection successful!" || echo "  ❌ Connection test failed — check the password"

echo ""
echo "============================================"
echo "  Setup Complete!"
echo "  Database : $DB_NAME"
echo "  User     : $DB_USER"
echo "  Tables   : payments, payment_logs"
echo "============================================"
echo ""
echo "Next steps:"
echo "  1. Pull latest code:  cd /path/to/website && git pull origin main"
echo "  2. Test payments on:  https://srms.lsc.edu.zm/db_ping.php"
echo "  3. Delete db_ping.php from the server when done"
echo ""
