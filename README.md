# Google Drive Offload

**Plugin Name**: Google Drive Offload  
**Description**: Offload WordPress media files to Google Drive, freeing up server space and utilizing Google Drive for storage.  
**Version**: 1.0  
**Author**: Sivuyile Parkies  

## Description
Google Drive Offload is a WordPress plugin that automatically transfers uploaded media files to Google Drive. 
It helps reduce server storage load and manage media files efficiently by utilizing Google Drive as an external storage solution.

## Requirements
- **PHP**: 8.0+
- **WordPress**: 5.8+
- **Google API PHP Client** (autoloaded in `vendor` folder)
- **Composer**: Make sure Composer is installed to manage dependencies.
- **npm**: If any frontend dependencies or build steps are required, install npm and run `npm install` after setting up.

## Installation
1. **Upload the Plugin**  
   Upload the `google-drive-offload` directory to the `/wp-content/plugins/` directory.
   
2. **Activate the Plugin**  
   Activate the plugin through the 'Plugins' menu in WordPress.

3. **Set Up Google API Credentials**  
   Before using the plugin, you'll need to configure Google API credentials.

## Google API Setup
1. **Create a Google Cloud Project**  
   - Visit [Google Cloud Console](https://console.cloud.google.com/).
   - Create a new project or select an existing project.

2. **Enable the Google Drive API**  
   - In your project dashboard, navigate to **APIs & Services > Library**.
   - Search for "Google Drive API" and enable it.

3. **Configure OAuth Consent Screen**  
   - Go to **APIs & Services > OAuth consent screen**.
   - Configure the consent screen with your app details.

4. **Create OAuth Credentials**  
   - Go to **APIs & Services > Credentials** and click **Create Credentials**.
   - Select **OAuth 2.0 Client IDs**.
   - Set the **Authorized redirect URI** to match your admin URL, for example:
     - `https://your-site.com/wp-admin/admin.php?page=google-drive-offload`

5. **Update Plugin Configuration**  
   - In your plugin settings, add your **Client ID** and **Client Secret**.
   - _Note: Use environment variables or a secure location for storing API keys if making your repository public._

## Usage
1. **Access Settings**  
   - After activation, go to **Settings > Google Drive Offload** to authenticate the plugin with Google Drive.

2. **Offload Media Files**  
   - Once authenticated, the plugin will automatically offload new media files to Google Drive.

## Contributing
Contributions are welcome! Please follow the typical fork-pull request workflow.

## Security Notice
Make sure to omit sensitive data (such as `Client ID` and `Client Secret`) if you upload this plugin to a public repository.

## License
This project is licensed under the MIT License.
