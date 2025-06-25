# Scandiweb Test Backend

This is a read-only backend API for products and categories, built with PHP (OOP), MySQL, REST, and GraphQL.

## Features
- Read-only REST API for products and categories
- Read-only GraphQL API for products and categories
- Data is seeded from a JSON file

---

## Setup Instructions

### 1. Clone the repository
```bash
git clone <your-repo-url>
cd folder-name
```

### 2. Install dependencies
```bash
composer install
```

### 3. Configure environment variables
Copy the example env file and edit as needed:
```bash
cp env.example .env
```
Edit `.env` to match your MySQL credentials and database name.

### 4. Create the database and tables
Log in to MySQL and create the database:
```sql
CREATE DATABASE scandiweb_test;
```
Then run the schema:
```bash
mysql -u <user> -p scandiweb_test < database/schema.sql
```

### 5. Seed the database with data
Place your `data.json` file in the `database/` folder, then run:
```bash
php database/seed.php
```

---

## Running the Server

Start the PHP built-in server:
```bash
php -S localhost:8000 -t public
```

---

## API Usage

### REST Endpoints
- `GET /api/categories` — List all categories
- `GET /api/products` — List all products
- `GET /api/products/{id}` — Get a product by ID
- `GET /api/products/search?q=term` — Search products by name/description/category

### GraphQL Endpoint
- `POST /graphql`

#### Example Query
```graphql
{
  categories { name }
  products { id name category brand }
}
```

---

## Notes
- The API is **read-only**: no create, update, or delete operations are allowed.
- Data is loaded from your `data.json` file via the seeder script.

---

## License
MIT 