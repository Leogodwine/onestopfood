# Food Order Delivery System

A Laravel-based direct chef-to-customer food ordering and delivery platform, designed for the Tanzanian market.

## Features Implemented

### Core Functionality
- ✅ **User Management** - Registration with roles (Customer, Chef, Traveler, Admin)
- ✅ **Role-Based Access Control** - Middleware protection for different user types
- ✅ **Account Approval System** - Chefs and Travelers require admin approval before operating
- ✅ **Meal Management** - Chefs can create and manage meal listings
- ✅ **Shopping Cart** - Session-based cart for customers
- ✅ **Order Processing** - Complete order flow from checkout to delivery
- ✅ **Payment Integration** - Support for M-Pesa, Tigo Pesa, Airtel Money, Card, and Cash on Delivery
- ✅ **Delivery Management** - Automatic traveler assignment and status tracking
- ✅ **Review System** - Customers can rate chefs and travelers
- ✅ **Admin Dashboard** - Approve/reject users, view pending accounts

### User Roles

1. **Customer** - Browse meals, place orders, track deliveries, leave reviews
2. **Chef** - Create meals, manage orders, accept/reject orders, update order status
3. **Traveler** - Toggle online/offline, accept deliveries, update delivery status
4. **Admin** - Approve/reject chef/traveler accounts, suspend users, view all users

## Setup Instructions

### Prerequisites
- PHP 8.2+
- Composer
- MySQL or SQLite
- Laravel 12

### Installation

1. **Install dependencies:**
   ```bash
   composer install
   ```

2. **Configure environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Configure database** (edit `.env`):
   ```env
   DB_CONNECTION=sqlite
   # OR for MySQL:
   # DB_CONNECTION=mysql
   # DB_HOST=127.0.0.1
   # DB_PORT=3306
   # DB_DATABASE=food_order_delivery
   # DB_USERNAME=root
   # DB_PASSWORD=
   ```

   If using SQLite, create the database file:
   ```bash
   touch database/database.sqlite
   ```

4. **Run migrations:**
   ```bash
   php artisan migrate
   ```

5. **Seed admin user:**
   ```bash
   php artisan db:seed --class=AdminUserSeeder
   ```
   Admin credentials: `admin@fooddelivery.com` / `password`

6. **Start development server:**
   ```bash
   php artisan serve
   ```

## Usage Flow

### For Customers:
1. Register/Login as Customer
2. Browse meals on homepage
3. Add meals to cart
4. Checkout and select payment method
5. Track order status
6. Review after delivery

### For Chefs:
1. Register as Chef (status: pending)
2. Wait for admin approval
3. Once approved, create meals
4. Receive and manage orders
5. Accept/reject orders
6. Update order status (preparing → ready)

### For Travelers:
1. Register as Traveler (status: pending)
2. Wait for admin approval
3. Once approved, toggle online
4. Accept available deliveries
5. Update delivery status (picked up → delivered)

### For Admins:
1. Login as admin
2. View pending chefs/travelers
3. Review user details and documents
4. Approve or reject accounts
5. Suspend/unsuspend users

## Database Schema

Key tables:
- `users` - All platform users with roles
- `meals` - Chef meal listings
- `orders` - Customer orders
- `order_items` - Order line items
- `payments` - Payment records
- `deliveries` - Delivery assignments
- `reviews` - Customer reviews
- `chef_profiles` - Chef-specific information
- `traveler_profiles` - Traveler-specific information
- `user_verification_documents` - Verification documents
- `locations` - User addresses/locations

## Payment Methods Supported

- **M-Pesa** (Vodacom)
- **Tigo Pesa**
- **Airtel Money**
- **Card** (via payment gateway)
- **Cash on Delivery (COD)**

*Note: Payment gateway integrations are stubbed. Real API integration would be added in production.*

## Next Steps / Future Enhancements

- Real-time order tracking with maps
- Push notifications
- SMS notifications
- Advanced search and filtering
- Chef earnings dashboard
- Traveler earnings withdrawal
- Image uploads for meals and documents
- Multi-chef orders
- Order scheduling
- Promo codes and discounts
- Analytics dashboard

## Documentation

See the `docs/` folder for:
- Project proposal and requirements
- Database ERD
- SDLC documentation

## License

This project is part of an academic/research project by Godwine Leo (LEOG Company).
