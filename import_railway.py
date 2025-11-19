"""
Script untuk import database ke Railway MySQL
Gunakan ini jika mysql client tidak berfungsi
"""

import subprocess
import sys

# Railway MySQL credentials
HOST = "metro.proxy.rlwy.net"
PORT = "39338"
USER = "root"
PASSWORD = "MClfPGdILKmmIxLDjwgZesirmxvnXqiQ"
DATABASE = "railway"

SQL_FILES = ["database.sql", "insert_default_data.sql"]

print("=" * 50)
print("Railway MySQL Import Script (Python)")
print("=" * 50)
print(f"\nHost: {HOST}")
print(f"Port: {PORT}")
print(f"Database: {DATABASE}\n")

# Check if mysql is available
mysql_path = r"C:\xampp2\mysql\bin\mysql.exe"

try:
    for sql_file in SQL_FILES:
        print(f"Importing {sql_file}...")
        
        # Read SQL file
        with open(sql_file, 'r', encoding='utf-8') as f:
            sql_content = f.read()
        
        # Build mysql command
        cmd = [
            mysql_path,
            f"-h{HOST}",
            f"-P{PORT}",
            f"-u{USER}",
            f"-p{PASSWORD}",
            DATABASE,
            "--default-character-set=utf8"
        ]
        
        # Execute
        process = subprocess.Popen(
            cmd,
            stdin=subprocess.PIPE,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            text=True
        )
        
        stdout, stderr = process.communicate(input=sql_content)
        
        if process.returncode == 0:
            print(f"✅ SUCCESS: {sql_file} imported\n")
        else:
            print(f"❌ ERROR: Failed to import {sql_file}")
            print(f"Error: {stderr}\n")
            sys.exit(1)
    
    print("=" * 50)
    print("✅ All files imported successfully!")
    print("=" * 50)
    print("\nDefault Login:")
    print("- Admin: admin / password")
    print("- Petugas: petugas / password")
    print("- User: user / password")
    
except FileNotFoundError as e:
    print(f"❌ ERROR: {e}")
    print("\nPastikan file database.sql dan insert_default_data.sql ada di folder ini")
    
except Exception as e:
    print(f"❌ ERROR: {e}")
    print("\nGunakan cara alternatif:")
    print("1. Railway Dashboard Query Editor (RECOMMENDED)")
    print("2. TablePlus (https://tableplus.com)")
    print("3. DBeaver (https://dbeaver.io)")

input("\nPress Enter to exit...")
