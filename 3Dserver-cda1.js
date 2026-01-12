const fs = require('fs');
const http = require('http');
const path = require('path');

const port = 8080;
const directoryName = './';
const root = path.normalize(path.resolve(directoryName));

const types = {
  html: 'text/html',
  css: 'text/css',
  js: 'application/javascript',
  png: 'image/png',
  jpg: 'image/jpeg',
  jpeg: 'image/jpeg',
  gif: 'image/gif',
  json: 'text/json',
  xml: 'application/xml',
  wav: 'audio/wav',
  mp4: 'vidio/mp4',
  mp3: 'audio/mp3',
  woff: 'text/woff',
  woff2: 'text/woff2',
  pdb: 'text/pdb'
};

const server = http.createServer((request, response) => {
  console.log(`${request.method} ${request.url}`);

  // --- API Endpoints ---

  if (request.url === '/api/music') {
    const musicDir = path.join(root, 'music');
    fs.readdir(musicDir, (err, files) => {
      if (err) {
        response.writeHead(500, { 'Content-Type': 'application/json' });
        response.end(JSON.stringify({ error: err.message }));
        return;
      }
      const musicFiles = files.filter(f => ['.mp3', '.wav'].includes(path.extname(f).toLowerCase()));
      response.writeHead(200, { 'Content-Type': 'application/json' });
      response.end(JSON.stringify(musicFiles));
    });
    return;
  }

  if (request.url === '/api/images') {
    const imgDir = path.join(root, 'images');
    fs.readdir(imgDir, (err, files) => {
      if (err) {
        response.writeHead(500, { 'Content-Type': 'application/json' });
        response.end(JSON.stringify({ error: err.message }));
        return;
      }
      // Filter primarily for images
      const imgFiles = files.filter(f => ['.png', '.jpg', '.jpeg', '.gif'].includes(path.extname(f).toLowerCase()));
      response.writeHead(200, { 'Content-Type': 'application/json' });
      response.end(JSON.stringify(imgFiles));
    });
    return;
  }

  if (request.url.startsWith('/api/presets')) {
    const urlParams = new URLSearchParams(request.url.split('?')[1]);
    const type = urlParams.get('type'); // 'AOM' or 'LIS'
    
    const presetDir = path.join(root, 'presets');
    fs.readdir(presetDir, (err, files) => {
      if (err) {
        response.writeHead(500, { 'Content-Type': 'application/json' });
        response.end(JSON.stringify({ error: err.message }));
        return;
      }
      
      let filtered = files;
      if (type === 'AOM') {
        filtered = files.filter(f => f.startsWith('Data_AOM_') && f.endsWith('.json'));
      } else if (type === 'LIS') {
        filtered = files.filter(f => f.startsWith('Data_LIS_') && f.endsWith('.json'));
      } else {
         filtered = files.filter(f => f.endsWith('.json'));
      }

      response.writeHead(200, { 'Content-Type': 'application/json' });
      response.end(JSON.stringify(filtered));
    });
    return;
  }

  // --- File Browser Endpoint ---
  if (request.url.startsWith('/api/browse')) {
      const urlParams = new URLSearchParams(request.url.split('?')[1]);
      let browsePath = urlParams.get('dir') || root;
      
      // Decode path
      browsePath = decodeURIComponent(browsePath);

      // Security / Error handling for path
      try {
          // Check if path exists and is a directory
          if (!fs.existsSync(browsePath) || !fs.lstatSync(browsePath).isDirectory()) {
             // Fallback to root if invalid
             browsePath = root;
          }

          fs.readdir(browsePath, { withFileTypes: true }, (err, entries) => {
              if (err) {
                  response.writeHead(500, { 'Content-Type': 'application/json' });
                  response.end(JSON.stringify({ error: err.message }));
                  return;
              }

              const result = entries.map(entry => {
                  const isDir = entry.isDirectory();
                  return {
                      name: entry.name,
                      type: isDir ? 'dir' : 'file',
                      path: path.join(browsePath, entry.name)
                  };
              }).filter(item => {
                  if (item.type === 'dir') return true;
                  const ext = path.extname(item.name).toLowerCase();
                  return ['.mp3', '.wav'].includes(ext);
              });

              // Sort: Directories first, then files (alphabetical)
              result.sort((a, b) => {
                  if (a.type === b.type) return a.name.localeCompare(b.name);
                  return a.type === 'dir' ? -1 : 1;
              });

              const responseData = {
                  path: browsePath,
                  parent: path.dirname(browsePath),
                  files: result
              };

              response.writeHead(200, { 'Content-Type': 'application/json' });
              response.end(JSON.stringify(responseData));
          });
      } catch (e) {
          response.writeHead(500, { 'Content-Type': 'application/json' });
          response.end(JSON.stringify({ error: e.message }));
      }
      return;
  }

  if (request.url.startsWith('/presets/')) {
    const presetPath = path.join(root, decodeURIComponent(request.url));
    console.log(`Serving preset: ${presetPath}`);
    fs.readFile(presetPath, (err, data) => {
      if (err) {
        console.error(`Error reading preset ${presetPath}:`, err);
        response.writeHead(404, { 'Content-Type': 'application/json' });
        response.end(JSON.stringify({ error: 'Preset not found' }));
      } else {
        response.writeHead(200, { 'Content-Type': 'application/json' });
        response.end(data);
      }
    });
    return;
  }

  // --- Static File Serving ---

  // Strip query string for static file serving
  const cleanUrl = request.url.split('?')[0];
  
  const extension = path.extname(cleanUrl).slice(1);
  const type = extension ? types[extension] : types.html;
  const supportedExtension = Boolean(type);

  if (!supportedExtension) {
    response.writeHead(404, { 'Content-Type': 'text/html' });
    response.end('404: File not found - Unsupported Extension');
    return;
  }

  let fileName = cleanUrl;
  if (cleanUrl === '/') fileName = 'index.html';
  else if (!extension) {
    // Try to find html file
    try {
        const potentialHtml = path.join(root, cleanUrl + '.html');
        if (fs.existsSync(potentialHtml)) {
             fileName = cleanUrl + '.html';
        } else {
             fileName = path.join(cleanUrl, 'index.html');
        }
    } catch (e) {
      fileName = path.join(cleanUrl, 'index.html');
    }
  }

  // Handle URL decoding (spaces, etc.)
  fileName = decodeURIComponent(fileName);

  const filePath = path.join(root, fileName);
  
  // Security check: Ensure path is within root, UNLESS it's an absolute path requested via the specific file playing mechanism
  // For this local tool, we will allow reading absolute paths if they are passed in decodeURIComponent(fileName) and exist.
  // However, the browser usually requests resources relative to the domain.
  // We need to intercept requests that look like absolute paths.
  // NOTE: A standard browser request to http://localhost:8080/C:/Windows... is treated as relative. 
  // We need a specific endpoint to serve arbitrary files or handle the path logic carefully.
  
  // Let's modify the file serving logic to check if 'cleanUrl' (after slash removal) is an absolute path on the system.
  // On Windows, /C:/... might come in.
  
  let finalPath = filePath;
  // Check if we are trying to serve an absolute path (from the file browser)
  // Remove leading slash from URL if present to check for drive letter
  let potentialAbsolutePath = cleanUrl.startsWith('/') ? cleanUrl.slice(1) : cleanUrl;
  potentialAbsolutePath = decodeURIComponent(potentialAbsolutePath);
  
  if (path.isAbsolute(potentialAbsolutePath) && fs.existsSync(potentialAbsolutePath)) {
      finalPath = potentialAbsolutePath;
  }
  
  // If it's not absolute, we stick to the root-based logic (which is already in filePath)
  // But we still want to block access to sensitive system files if needed, but for this 'local drive' request, we'll allow it.

  fs.readFile(finalPath, (err, data) => {
    if (err) {
      console.log(`Error reading ${finalPath}: ${err}`);
      response.writeHead(404, { 'Content-Type': 'text/html' });
      response.end('404: File not found');
    } else {
      response.writeHead(200, { 'Content-Type': type });
      response.end(data);
    }
  });
});

server.listen(port, () => {
  console.log(`3D Server is listening on port ${port}`);
});
