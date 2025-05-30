# Insta App

A modern Instagram-like social media application built with Laravel, Vue.js, TypeScript, and Inertia.js. This application allows users to share posts, like content, and engage through comments.

## Features

- User authentication and authorization
- Post creation and sharing
- Like/unlike functionality
- Commenting system
- User profiles
- Responsive design
- Real-time updates

## Tech Stack

- **Backend:** Laravel 12
- **Frontend:** Vue 3 with TypeScript
- **UI Framework:** Tailwind CSS with shadcn-vue components
- **Database:** MySQL 8
- **Authentication:** Laravel Breeze
- **API Architecture:** Inertia.js

## Prerequisites

- PHP >= 8.1
- Node.js >= 16.x
- Composer
- Git
- MySQL >= 5.7

## Installation

1. Clone the repository:
   ```bash
   git clone <your-repo-url>
   cd insta-app-sev
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install Node.js dependencies:
   ```bash
   npm install
   ```

4. Set up environment variables:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. Configure your database in `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=instaapp_sev
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. Create MySQL database:
   ```bash
   mysql -u root -e "CREATE DATABASE instaapp-sev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

7. Run database migrations and seeders:
   ```bash
   php artisan migrate --seed
   ```

8. Create storage link for public files:
   ```bash
   php artisan storage:link
   ```

## Running the Application

1. Start the development server:
   ```bash
   composer run dev
   ```

The application will be accessible at `http://127.0.0.1:8000`


## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
