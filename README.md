# POS Management System

A modern, enterprise-grade Point of Sale (POS) management system built with Vue 3 (Frontend), PHP 8.1+ (Backend), and MySQL 8.0+ (Database).

## ✨ Features

### Core Functionality
- **Inventory Management** - Real-time stock tracking, product management with images
- **Point of Sale Checkout** - Fast, intuitive checkout with barcode scanning support
- **Customer Management** - Customer profiles, purchase history, loyalty tracking
- **Receipt Printing** - Professional receipt generation, printing, and email
- **Sales Reports** - Daily, weekly, monthly analytics and insights
- **Payment Processing** - Multiple payment methods (Cash, Card, Mobile)

### Advanced Features
- **Role-Based Access Control** - Admin, Manager, Cashier with granular permissions
- **Multi-Currency Support** - Handle multiple currencies
- **Tax Management** - Automatic tax calculations
- **Discount System** - Fixed, percentage, and promotional discounts
- **User Authentication** - Secure JWT-based authentication
- **Dashboard Analytics** - Real-time sales overview with charts
- **Audit Logging** - Complete transaction history
- **Product Categories** - Organized product management
- **Stock Alerts** - Low stock notifications

## 🏗️ Project Structure

```
POS-SYSTEM/
├── backend/                 # PHP REST API
├── frontend/                # Vue 3 Application
├── database/                # MySQL Schema
├── docs/                    # Documentation
└── docker-compose.yml       # Docker setup
```

## 🚀 Tech Stack

- **Frontend**: Vue 3, Pinia, Axios, Chart.js, Tailwind CSS, Vite
- **Backend**: PHP 8.1+, RESTful API, JWT Authentication
- **Database**: MySQL 8.0+
- **Architecture**: MVC, Service Layer, Repository Pattern

## 📋 Quick Start

```bash
docker-compose up -d
```

Access:
- Frontend: http://localhost:5173
- Backend API: http://localhost:8000/api
- MySQL: localhost:3306

## 📚 Documentation

- [API Documentation](./docs/API.md)
- [Installation Guide](./docs/INSTALLATION.md)
- [Database Schema](./database/schema.sql)

## 📄 License

MIT
