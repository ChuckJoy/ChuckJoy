# Project Context: Chuck Joy's 3D Graphics Playground

## Project Overview

This project is a web-based 3D visualization and audio experimentation playground. It leverages **Three.js** for rendering 3D graphics and the **Web Audio API** for real-time audio analysis (FFT) and visualization. The application creates interactive "constellations" of geometric shapes that react to music or microphone input.

**Current Architecture (Jan 2026):**
The project has migrated from a custom Node.js server to a standard **LAMP (Linux/Windows, Apache, MySQL, PHP)** stack running on XAMPP. This enables robust database storage for presets, music indexing, and image snapshots.

## Key Components

### 1. Root Application (Active Development)

*   **Entry Points:**
    *   `index.html`: The main dashboard linking to various demos.
    *   `LostInSpace.html` & `LostInSpaceFFT.html`: The primary 3D visualization demos. Updated to fetch config/presets from the MySQL database via `api_v2.php`.
    *   `AOM-M6-helix-mic-LOOP-client.html`: "Art of Motion" demo. Database-integrated.
    *   `AudioDemoSandbox.html`, `DIFFCAM1.html`: Experimental sandboxes.

*   **Backend & Database:**
    *   **Server:** Apache (XAMPP).
    *   **API:** `api_v2.php` (Replaces `api.php`) - Handles JSON requests, base64 image uploads, preset management, and music listings.
    *   **Database:** MySQL DB `tunetolight`.
        *   `presets`: Stores visual configuration JSONs (`data`) and screenshot paths (`image_path`).
        *   `music`: Indexes available audio files.
    *   **Utilities:**
        *   `populate_db.php`: One-time script to ingest file-system assets into the DB.
        *   `update_schema.sql`: Updates DB schema for new features (e.g., images).
        *   `db_connect.php`: Database connection credentials.

*   **Assets:**
    *   `music/`: Audio files (.mp3, .wav).
    *   `presets/`: Legacy JSON files (now ingested into DB).
    *   `images/` & `textures/`: Texture assets.
    *   `build/` & `jsm/`: Three.js libraries.

### 2. Vite Client (`client/`)

*   **Structure:** Standard Vite project with `package.json`.
*   **Status:** Separate module, potentially for future modern frontend rewrite.

## Usage & Commands

### Running the Application (XAMPP)

1.  **Start XAMPP:** Ensure Apache and MySQL are running.
2.  **Deploy:** Files must be in `C:\xampp\htdocs\tunetolight`.
    *   **CRITICAL:** Ensure `build/`, `jsm/`, `images/`, `music/`, and `presets/` folders are present in the `tunetolight` directory for `three.module.js` and assets to load correctly.
3.  **Access:** Open `http://localhost/tunetolight/index.html`

### Database Setup
If setting up for the first time:
1.  Import `setup.sql` via phpMyAdmin.
2.  Run `http://localhost/tunetolight/populate_db.php` to index existing files.

## Recent Fixes & Updates

*   **API V2:** Migrated to `api_v2.php` to prevent HTML error output from corrupting JSON responses.
*   **Keyboard Shortcuts:** Fixed `KeyHandler` in `LostInSpace` apps to prevent "Duplicate Identifier" errors and removed spaces from hardcoded preset filenames.
*   **Screenshots:** `save()` function now captures the WebGL canvas as a PNG and saves it to the server/database alongside the preset data.
*   **Imports:** `three.module.js` loading issues resolved by ensuring asset folders are correctly deployed to the web server root.

## Key Files to Reference

*   `GEMINI.md`: Project context.
*   `api_v2.php`: Core backend logic.
*   `LostInSpace.html`: Main visualizer logic.
*   `update_schema.sql`: Latest DB schema changes.
