# Social Blog Web Application

A social blogging platform built with PHP and MySQL (PDO). Allows users to register, post, react, comment, upload images, and explore others’ profiles.  
Includes admin/user roles and secure session management.

## Features

- User registration, login, and logout system.
- Create new posts with title, content, and image upload.
- Edit or delete posts (only if you are the post owner).
- Each post includes a dynamic view of all related comments in an eye-friendly interface.
- Users can comment on their own posts and others’ posts, using a dynamic textarea that appears with a button click.
- Like and Dislike system for posts.
- Each user has a personal profile page with the ability to upload a profile picture.

## Technologies Used

- PHP 8.2.12
- MySQL
- XAMPP

## 🛠️ Installation and Setup

1. Download the project:

   ```bash
   git clone https://github.com/yousefelnopi/blog_php.git
   ```

2. Copy Project Files to XAMPP Folder:

   Copy the `blog_php` folder into the `htdocs` directory of your XAMPP installation, for example:

   `
   C:\xampp\htdocs\
   `

3. Start XAMPP:

   Open the XAMPP Control Panel and start the Apache and MySQL services.

4. Create the Database and Import Tables:

   Open your web browser and go to [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/).

   Create a new database named:

   `blog_php`

   Select the `blog_php` database, then go to the Import tab.

   Choose the `blog_php_tables.sql` file located in the project root (`My-Blog/blog_php_tables.sql`) and click Go to import the tables.

5. Configure Database Connection (if necessary):

   Open the file `config/db.php`.

   Ensure the database connection settings match the default XAMPP setup:

   ```php
   $host = 'localhost';
   $db   = 'blog_php';
   $user = 'root';
   $pass = '';
   ```

   Modify the values if your setup differs.

6. Run the Application:

   Open your browser and navigate to:

   `
   http://localhost/blog_php/public/index.php
   `

   You can now register, create posts, comment, and interact with other users.

## Notes

 Uploaded images are saved automatically in the `upload/` folder.
 Any user can register directly from the registration page.
 Features like Search and Pagination will be added in future updates.

## Contribution

If you want to contribute to the project, you can fork the repo and open a pull request.

## License

This project is licensed under the MIT License

README.md
