# News Aggregator API

This is a Laravel-based News Aggregator API that provides user authentication, article management, user preferences, and data aggregation. It uses **Laravel Sanctum** for API token authentication and **Docker** for environment management.

## Features

### 1. User Authentication
- Registration, login, logout, and password reset functionality.
- Secure token-based authentication using **Laravel Sanctum**.

### 2. Article Management
- Fetch paginated articles.
- Search and filter articles by date, author, and source.
- Retrieve detailed information for a specific article.

### 3. User Preferences
- Set and retrieve preferred news sources, and authors.
- Personalized news feed based on user preferences.

### 4. Data Aggregation
- Regularly fetch and store articles from 3 external news APIs.

### 5. API Documentation
- Comprehensive documentation available via **Swagger/OpenAPI**.

---

## Setup Instructions

### Prerequisites
- **Docker**: Ensure Docker and Docker Compose are installed on your machine.
- **Composer**: Install Composer globally for dependency management.

### Steps to Run the Application

1. **Clone the Repository**
   ```bash
   git clone <repository-url>
   cd <repository-folder>

2. **Build and Start Docker Containers:**
   ```bash
   docker-compose up -d

3. **Install Dependencies:**
   - Access the Docker container & Install dependencies:
   ```bash
   docker exec -it <container-name> bash
   composer install
   php artisan key:generate
   php artisan schedule

4. **Access the Application:**
  - **API Base URL**: http://localhost:8000
  - **Swagger Docs**: http://localhost:8000/api/documentation

---

## Additional Notes


The application fetches data from external news APIs on a scheduled basis using Laravel's task scheduler.


**Supported APIs include**:
  - News API
  - The Guardian API
  - New York Times API
