<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Document Reviewer</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .login-form { max-width: 300px; margin: 100px auto; padding: 20px; border: 1px solid #ddd; }
        .form-group { margin-bottom: 15px; }
        input, select, button { width: 100%; padding: 8px; margin: 5px 0; }
        button { background: #007cba; color: white; border: none; cursor: pointer; }
        button:hover { background: #005a87; }
        .document-list { display: flex; flex-wrap: wrap; gap: 20px; margin: 20px 0; }
        .document-card { border: 1px solid #ddd; padding: 15px; width: 300px; }
        .status-pending { border-left: 4px solid #ffa500; }
        .status-review { border-left: 4px solid #007cba; }
        .status-complete { border-left: 4px solid #28a745; }
        .comments { margin-top: 20px; max-height: 300px; overflow-y: auto; }
        .comment { padding: 10px; border-bottom: 1px solid #eee; }
        .comment-form { margin-top: 20px; }
        textarea { height: 60px; }
        .hidden { display: none; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="container">
    <!-- Login Section -->
    <div id="login-section">
        <div class="login-form">
            <h2>Login</h2>
            <div class="form-group">
                <input type="text" id="username" placeholder="Username">
            </div>
            <div class="form-group">
                <input type="password" id="password" placeholder="Password">
            </div>
            <button onclick="login()">Login</button>
        </div>
    </div>

    <!-- Main Application -->
    <div id="app-section" class="hidden">
        <div class="header">
            <h1>Document Reviewer</h1>
            <div>
                <span id="current-user"></span>
                <button onclick="logout()" style="width: auto; margin-left: 10px;">Logout</button>
            </div>
        </div>

        <!-- Upload Form -->
        <div>
            <h3>Upload Document</h3>
            <form id="upload-form" enctype="multipart/form-data">
                <input type="file" id="file-input" accept=".pdf,.docx" required>
                <button type="submit">Upload</button>
            </form>
        </div>

        <!-- Documents List -->
        <div>
            <h3>Documents</h3>
            <div id="documents-list" class="document-list"></div>
        </div>

        <!-- Document Viewer Modal -->
        <div id="document-modal" class="hidden" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000;">
            <div style="background: white; margin: 5% auto; padding: 20px; width: 80%; height: 80%; overflow-y: auto;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 id="modal-title">Document Viewer</h3>
                    <button onclick="closeModal()" style="width: auto;">Close</button>
                </div>
                
                <div>
                    <p id="doc-info"></p>
                    <div style="margin: 20px 0;">
                        Status: 
                        <select id="status-select" onchange="updateStatus()">
                            <option value="Pending Review">Pending Review</option>
                            <option value="Under Review">Under Review</option>
                            <option value="Review Complete">Review Complete</option>
                        </select>
                    </div>
                </div>

                <div id="comments-section">
                    <h4>Comments</h4>
                    <div id="comments-list" class="comments"></div>
                    
                    <div id="comment-form-section" class="comment-form hidden">
                        <textarea id="comment-input" placeholder="Add your comment..."></textarea>
                        <button onclick="addComment()">Add Comment</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Detect if running locally or in production
const API_BASE = window.location.hostname === 'localhost' ? 
    'http://localhost:5000/api' : 
    `${window.location.protocol}//${window.location.hostname}/api`;
let currentDocument = null;

// Check if user is already logged in
window.onload = function() {
    console.log('API_BASE:', API_BASE);
    testConnection();
    checkAuth();
};

async function testConnection() {
    try {
        console.log('Testing API connection...');
        const response = await fetch(`${API_BASE}/test`);
        const data = await response.json();
        console.log('API test successful:', data);
    } catch (error) {
        console.error('API test failed:', error);
        alert('Cannot connect to backend API. Please check if both containers are running.\nAPI URL: ' + API_BASE);
    }
}

async function checkAuth() {
    try {
        const response = await fetch(`${API_BASE}/check-auth`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.authenticated) {
            showApp(data.user);
        } else {
            showLogin();
        }
    } catch (error) {
        showLogin();
    }
}

async function login() {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    
    console.log('Attempting login with API:', API_BASE);
    
    try {
        const response = await fetch(`${API_BASE}/login`, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({ username, password })
        });
        
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Response data:', data);
        
        if (data.success) {
            showApp(data.user);
        } else {
            alert('Login failed: ' + data.message);
        }
    } catch (error) {
        console.error('Login error details:', error);
        alert('Login error: ' + error.message + '\nCheck console for details. API URL: ' + API_BASE);
    }
}

async function logout() {
    try {
        await fetch(`${API_BASE}/logout`, {
            method: 'POST',
            credentials: 'include'
        });
        showLogin();
    } catch (error) {
        console.error('Logout error:', error);
        showLogin();
    }
}

function showLogin() {
    document.getElementById('login-section').classList.remove('hidden');
    document.getElementById('app-section').classList.add('hidden');
}

function showApp(username) {
    document.getElementById('login-section').classList.add('hidden');
    document.getElementById('app-section').classList.remove('hidden');
    document.getElementById('current-user').textContent = `Welcome, ${username}`;
    loadDocuments();
    
    // Setup upload form
    document.getElementById('upload-form').onsubmit = uploadFile;
    
    // Auto-refresh documents every 10 seconds
    setInterval(loadDocuments, 10000);
}

async function uploadFile(e) {
    e.preventDefault();
    const fileInput = document.getElementById('file-input');
    const file = fileInput.files[0];
    
    if (!file) {
        alert('Please select a file');
        return;
    }
    
    const formData = new FormData();
    formData.append('file', file);
    
    try {
        const response = await fetch(`${API_BASE}/upload`, {
            method: 'POST',
            credentials: 'include',
            body: formData
        });
        
        const data = await response.json();
        if (data.success) {
            alert('File uploaded successfully');
            fileInput.value = '';
            loadDocuments();
        } else {
            alert('Upload failed: ' + data.error);
        }
    } catch (error) {
        alert('Upload error: ' + error.message);
    }
}

async function loadDocuments() {
    try {
        const response = await fetch(`${API_BASE}/documents`, {
            credentials: 'include'
        });
        const documents = await response.json();
        
        const listElement = document.getElementById('documents-list');
        listElement.innerHTML = '';
        
        documents.forEach(doc => {
            const card = document.createElement('div');
            card.className = `document-card status-${doc.status.toLowerCase().replace(' ', '-')}`;
            card.innerHTML = `
                <h4>${doc.filename}</h4>
                <p>Status: ${doc.status}</p>
                <p>Uploaded by: ${doc.uploaded_by}</p>
                <p>Date: ${new Date(doc.upload_date).toLocaleDateString()}</p>
                <button onclick="viewDocument('${doc.id}')">View</button>
            `;
            listElement.appendChild(card);
        });
    } catch (error) {
        console.error('Error loading documents:', error);
    }
}

async function viewDocument(docId) {
    try {
        const response = await fetch(`${API_BASE}/documents`, {
            credentials: 'include'
        });
        const documents = await response.json();
        const doc = documents.find(d => d.id === docId);
        
        if (!doc) return;
        
        currentDocument = doc;
        document.getElementById('modal-title').textContent = doc.filename;
        document.getElementById('doc-info').textContent = `Uploaded by ${doc.uploaded_by} on ${new Date(doc.upload_date).toLocaleDateString()}`;
        document.getElementById('status-select').value = doc.status;
        
        // Show/hide comment form based on status
        const commentFormSection = document.getElementById('comment-form-section');
        if (doc.status === 'Under Review') {
            commentFormSection.classList.remove('hidden');
        } else {
            commentFormSection.classList.add('hidden');
        }
        
        loadComments(docId);
        document.getElementById('document-modal').classList.remove('hidden');
        
        // Auto-refresh comments every 5 seconds
        if (window.commentsInterval) clearInterval(window.commentsInterval);
        window.commentsInterval = setInterval(() => loadComments(docId), 5000);
        
    } catch (error) {
        console.error('Error viewing document:', error);
    }
}

async function loadComments(docId) {
    try {
        const response = await fetch(`${API_BASE}/document/${docId}/comments`, {
            credentials: 'include'
        });
        const comments = await response.json();
        
        const commentsElement = document.getElementById('comments-list');
        commentsElement.innerHTML = '';
        
        comments.forEach(comment => {
            const commentDiv = document.createElement('div');
            commentDiv.className = 'comment';
            commentDiv.innerHTML = `
                <strong>${comment.user}</strong> 
                <small>(${new Date(comment.timestamp).toLocaleString()})</small>
                <p>${comment.comment}</p>
            `;
            commentsElement.appendChild(commentDiv);
        });
    } catch (error) {
        console.error('Error loading comments:', error);
    }
}

async function addComment() {
    const commentInput = document.getElementById('comment-input');
    const comment = commentInput.value.trim();
    
    if (!comment || !currentDocument) return;
    
    try {
        const response = await fetch(`${API_BASE}/document/${currentDocument.id}/comments`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ comment })
        });
        
        const data = await response.json();
        if (data.success) {
            commentInput.value = '';
            loadComments(currentDocument.id);
        } else {
            alert('Failed to add comment: ' + data.error);
        }
    } catch (error) {
        alert('Error adding comment: ' + error.message);
    }
}

async function updateStatus() {
    const newStatus = document.getElementById('status-select').value;
    
    if (!currentDocument) return;
    
    try {
        const response = await fetch(`${API_BASE}/document/${currentDocument.id}/status`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ status: newStatus })
        });
        
        const data = await response.json();
        if (data.success) {
            currentDocument.status = newStatus;
            // Show/hide comment form based on new status
            const commentFormSection = document.getElementById('comment-form-section');
            if (newStatus === 'Under Review') {
                commentFormSection.classList.remove('hidden');
            } else {
                commentFormSection.classList.add('hidden');
            }
            loadDocuments(); // Refresh the main list
        } else {
            alert('Failed to update status: ' + data.error);
        }
    } catch (error) {
        alert('Error updating status: ' + error.message);
    }
}

function closeModal() {
    document.getElementById('document-modal').classList.add('hidden');
    currentDocument = null;
    if (window.commentsInterval) {
        clearInterval(window.commentsInterval);
    }
}
</script>

</body>
</html>