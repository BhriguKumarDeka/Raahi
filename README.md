# Raahi: Collaborative Group Travel Planner

Raahi is a modern collaborative web application designed to simplify the logistics of planning group travel. It provides a central workspace where group members can collectively design itineraries, discuss details, vote on ideas, share files, and manage expenses.

## System Overview

Raahi organizes travel planning around collaborative trip workspaces. Users onboard by specifying their travel styles and planning preferences, which are used to personalize suggestions. 

### Key Capabilities

*   **Interactive Dashboard**: A full-width view showcasing upcoming, planning, completed, and group-invited trips. It contains real-time stat cards, layout toggle options (grid and list views), status tabs, and dynamic search capabilities.
*   **Trip Workspace**:
    *   **Overview**: Summary dashboard containing a trip countdown, description, budget estimate, and member list.
    *   **Itinerary Planning**: Day-by-day collaborative timeline to add, view, and edit activities.
    *   **Shared Budget & settlements**: An expense ledger allowing members to log costs. The system automatically calculates settlements (net balances and who owes whom) and breaks down spending by category (transportation, meals, accommodation, activities, etc.).
    *   **Group Polls**: Create polls to vote on destinations, accommodations, or dates with support for locking options.
    *   **Documents Storage**: Centralized directory to upload, list, and download flight tickets, reservation PDFs, and other travel documents.
    *   **Discussion Boards**: Threaded comments to maintain conversation context.
    *   **Members & Roles**: Manage member access and invite participants via system invitations.
*   **Curated Explorer**: Discover popular destinations (such as Bali, Kyoto, Patagonia, and Paris). View day-by-day itinerary previews and clone entire templates into active personal workspaces with a single click.
*   **Dynamic Cover Images & Caching**: Integrates Pexels API to automatically populate trip cards and workspaces with stunning, context-specific landscape images. Implements a query-level caching strategy (successes cached forever, transient errors/rate-limits cached for 10 minutes) and model lifecycle hooks to persist images in database columns, preventing redundant outbound API requests.
*   **Administration Panel**: A dedicated administrative dashboard to view global system stats, manage user access roles, and moderate trips.
*   **Responsive Navigation**: Includes quick "+ New Trip" creation actions, a global Notification Bell dropdown with pending invitations, and user account management.

## Technical Architecture

*   **Framework**: Laravel 13
*   **Language**: PHP 8.5
*   **Frontend Interactivity**: Livewire 3 (Volt functional API), Tailwind CSS v4, Alpine.js, Motion.dev
*   **Media Provider**: Pexels API (with persistent cache layer)
*   **Testing Suite**: Pest PHP

## Installation & Setup

Follow these steps to set up and run Raahi locally:

### Prerequisites

*   PHP 8.2 or higher
*   Composer
*   Node.js & NPM
*   SQLite or another compatible database driver

### Step-by-Step Installation

1.  **Clone the Repository**:
    ```bash
    git clone https://github.com/bhrigukumardeka/raahi.git
    cd raahi
    ```

2.  **Install PHP Dependencies**:
    ```bash
    composer install
    ```

3.  **Install Frontend Dependencies**:
    ```bash
    npm install
    ```

4.  **Configure Environment**:
    Copy the sample environment file and generate the application key:
    ```bash
    copy .env.example .env
    php artisan key:generate
    ```
    Ensure your database settings in `.env` are configured (by default, an SQLite database is used).

5.  **Run Database Migrations and Seeders**:
    Initialize the database structure and populate it with seed accounts:
    ```bash
    php artisan migrate --seed
    ```
    This seeds the application with the following test credentials (all passwords are `password`):
    *   System Admin: `admin@raahi.com`
    *   Organizer: `test@example.com`
    *   Co-Planner: `rohan@example.com`
    *   Member: `priya@example.com`
    *   Onboarding Demo User: `kabir@example.com`

6.  **Compile Assets**:
    ```bash
    npm run build
    ```

7.  **Start the Local Server**:
    ```bash
    php artisan serve
    ```
    Access the application at `http://127.0.0.1:8000`.

## Testing

To run the automated feature and unit tests:

```bash
vendor/bin/pest
```

All core routes, authorization controls, itinerary cloning, and invite flows are fully covered by the test suite.
