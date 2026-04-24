# SmartSchool Uniform Ordering System

A complete, production-ready PHP/MySQL uniform ordering system designed to replace Google Forms workflows.

## Features

### Parent Features
- User registration and login
- Product catalog browsing
- Dynamic size charts
- Shopping cart management
- M-Pesa payment integration
- Order history and tracking
- Email/SMS notifications

### Admin Features
- Inventory management
- Order processing dashboard
- User management
- Size chart editor
- Reporting and analytics
- Payment tracking

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

### Quick Setup

1. **Extract files** to your web root directory
2. **Create database** and import `smart_school_uniforms_complete.sql`
3. **Configure database** in `config/database.php`
4. **Set up environment** by copying `.env.example` to `.env`
5. **Access the application** at your domain

### Default Admin Login
- Email: admin@smartschool.com
- Password: password

## Configuration

### M-Pesa Integration
1. Register at Safaricom Developer Portal
2. Update M-Pesa credentials in `.env`
3. Configure callback URLs
4. Test with sandbox environment

### Email Setup
Configure SMTP settings in `.env` for order notifications.

## Security Features

- Input sanitization and validation
- SQL injection prevention
- XSS protection
- CSRF protection
- Session security
- Password hashing

## Support

For technical support contact: support@smartschool.com