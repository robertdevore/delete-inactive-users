# delete-inactive-users
The **Delete Inactive Users** WordPress plugin is a robust tool designed to help administrators efficiently manage user accounts by deleting inactive users based on their role and last login date. 

Ideal for optimizing database performance and maintaining user data hygiene, this plugin allows you to configure custom settings and batch process large datasets.

## Features

- **Customizable Role Selection**: Choose specific user roles to target for deletion.
- **Date-Based Inactivity**: Delete users who have not logged in since a specified date.
- **Batch Processing**: Handles large datasets efficiently by processing users in batches.
- **Progress Feedback**: Real-time progress bar and status messages during the deletion process.
- **Safe Operations**: Includes nonce verification and safeguards to ensure only authorized actions are performed.

## Installation

1. Download the plugin from the [GitHub repository](https://github.com/robertdevore/delete-inactive-users/).
2. Upload the plugin files to the `/wp-content/plugins/delete-inactive-users/` directory or install via the WordPress admin panel.
3. Activate the plugin through the 'Plugins' screen in WordPress.
4. Navigate to `Tools > Delete Users` in the WordPress admin panel to configure and use the plugin.
## Usage

1. **Access the Settings Page**
    - Go to `Tools > Delete Users` in the WordPress admin menu.

2. **Select User Role**
    - Choose the user role you want to target for deletion from the dropdown.

3. **Set Cutoff Date**
    - Use the date picker to specify the last login cutoff date. Users who have not logged in since this date will be targeted.

4. **Start Deletion**
    - Click the "Start Deletion" button. A progress bar and status messages will provide real-time updates.

5. **Completion Notification**
    - Once the process is complete, a success message will be displayed.

## Contributing

Contributions are welcome! Please fork the repository and submit a pull request.

1. Fork the repository.
2. Create a feature branch (`git checkout -b feature/feature-name`).
3. Commit your changes (`git commit -m 'Add feature'`).
4. Push to the branch (`git push origin feature-name`).
5. Open a pull request.

## License

This plugin is licensed under the GPL-2.0+ License. See the LICENSE file for details.

## Support

For support or inquiries, please open an issue on the [GitHub repository](https://github.com/robertdevore/delete-inactive-users/issues).
