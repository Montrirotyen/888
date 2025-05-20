git clone https://github.com/Montrirotyen/888.git
cd 888
composer install
cp .env.example .env
php artisan key:generate
# ตั้งค่า .env ให้ตรงกับ database
php artisan migrate
php artisan serve
