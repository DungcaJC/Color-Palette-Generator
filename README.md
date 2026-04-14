-----## Color Palette Generator ##-----

Integrate an AI or image API (like The Color API or Unsplashed)
to generate color palettes from a photo or keyword. 
Algorithm: extract dominant colors using a frequency/clustering approach. 
Let users save and export palettes.

-----## Features ##-----

- Generate color palettes from images or keywords.
- Save and export palettes in various formats (e.g., PNG, JSON).
- Create custom palettes.
- User-friendly interface for browsing and managing palettes.

-----## Installation Instructions ##-----

Need to install:
-Git (https://git-scm.com/) This is required to clone the repository.
-Node.js (https://nodejs.org/) This is required for the frontend development server, You need a command like "npm install" to install the frontend dependencies and "npm run dev" to start the development server.
-Composer (https://getcomposer.org/) This is required for the backend dependencies management, You need a command like "composer install" to install the Laravel framework dependencies.
-PHP (https://www.php.net/) This is required to run the backend server, because we need a PHP 8.3 or higher version to run the Laravel framework.
-Xampp (https://www.apachefriends.org/index.html) This is required to run the backend server because we need a local server environment to run the Laravel framework.
To set up the project, follow these steps:
1. Clone the repository:
    git clone <repository-url>
    cd <repository-name>
2. Install backend dependencies:
    cd BACKEND
    composer install
3. Install frontend dependencies:
    cd ../FRONTEND/Palette
    npm install
4. Start the backend server:
    php artisan serve
5. Start the frontend development server:
    npm run dev
6. Open your browser and navigate to http://localhost:3000 to access the application.
Note: Make sure to replace <repository-url> and <repository-name> with the actual URL and name of your repository.

-----## Start Application ##-----

Start the web: (use a Terminal for each command)
BACKEND: Start
C:\xampp\htdocs\Color-Palette-Generator\BACKEND> php artisan serve
FRONTEND: Start
C:\xampp\htdocs\Color-Palette-Generator\FRONTEND\Palette> npm run dev

note: Make sure to have the backend server running before starting the frontend development server, as the frontend will need to communicate with the backend API to fetch and manage color palettes.
note: The application will be accessible at http://localhost:3000, where you can generate, save, and manage your color palettes.
note: If you encounter any issues during installation or while running the application, please refer to the documentation of the respective tools (Git, Node.js, Composer, PHP, Xampp) for troubleshooting steps.
note: When starting the backend server, ensure that you are in the correct directory (BACKEND) where the Laravel application is located, and when starting the frontend development server, ensure that you are in the correct directory (FRONTEND/Palette) where the React application is located.
note: If you want to stop the servers, you can use Ctrl + C in the terminal where the servers are running.