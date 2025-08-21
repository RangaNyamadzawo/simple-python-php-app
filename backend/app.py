from flask import Flask, request, jsonify, session
from flask_cors import CORS
import os
import json
from datetime import datetime
import uuid

app = Flask(__name__)
app.secret_key = 'simple-secret-key'

# Configure CORS more permissively for development
CORS(app, 
     supports_credentials=True,
     origins=['*'],  # Allow all origins for now
     allow_headers=['Content-Type', 'Authorization'],
     methods=['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']
)

# Simple in-memory storage
users = {
    'admin': 'password123',
    'reviewer1': 'password123',
    'reviewer2': 'password123'
}

documents = {}
comments = {}

UPLOAD_FOLDER = '/app/uploads'
os.makedirs(UPLOAD_FOLDER, exist_ok=True)

@app.route('/api/login', methods=['POST'])
def login():
    data = request.get_json()
    username = data.get('username')
    password = data.get('password')
    
    if username in users and users[username] == password:
        session['user'] = username
        return jsonify({'success': True, 'user': username})
    
    return jsonify({'success': False, 'message': 'Invalid credentials'}), 401

@app.route('/api/logout', methods=['POST'])
def logout():
    session.pop('user', None)
    return jsonify({'success': True})

@app.route('/api/check-auth', methods=['GET'])
def check_auth():
    if 'user' in session:
        return jsonify({'authenticated': True, 'user': session['user']})
    return jsonify({'authenticated': False})

@app.route('/api/upload', methods=['POST'])
def upload_file():
    if 'user' not in session:
        return jsonify({'error': 'Not authenticated'}), 401
    
    if 'file' not in request.files:
        return jsonify({'error': 'No file provided'}), 400
    
    file = request.files['file']
    if file.filename == '':
        return jsonify({'error': 'No file selected'}), 400
    
    if file and (file.filename.endswith('.pdf') or file.filename.endswith('.docx')):
        doc_id = str(uuid.uuid4())
        filename = f"{doc_id}_{file.filename}"
        file_path = os.path.join(UPLOAD_FOLDER, filename)
        file.save(file_path)
        
        documents[doc_id] = {
            'id': doc_id,
            'filename': file.filename,
            'stored_filename': filename,
            'status': 'Pending Review',
            'uploaded_by': session['user'],
            'upload_date': datetime.now().isoformat()
        }
        
        return jsonify({'success': True, 'document': documents[doc_id]})
    
    return jsonify({'error': 'Invalid file type'}), 400

@app.route('/api/documents', methods=['GET'])
def get_documents():
    if 'user' not in session:
        return jsonify({'error': 'Not authenticated'}), 401
    
    return jsonify(list(documents.values()))

@app.route('/api/document/<doc_id>/status', methods=['PUT'])
def update_document_status(doc_id):
    if 'user' not in session:
        return jsonify({'error': 'Not authenticated'}), 401
    
    data = request.get_json()
    new_status = data.get('status')
    
    if doc_id in documents and new_status in ['Pending Review', 'Under Review', 'Review Complete']:
        documents[doc_id]['status'] = new_status
        return jsonify({'success': True, 'document': documents[doc_id]})
    
    return jsonify({'error': 'Document not found or invalid status'}), 400

@app.route('/api/document/<doc_id>/comments', methods=['GET'])
def get_comments(doc_id):
    if 'user' not in session:
        return jsonify({'error': 'Not authenticated'}), 401
    
    doc_comments = comments.get(doc_id, [])
    return jsonify(doc_comments)

@app.route('/api/document/<doc_id>/comments', methods=['POST'])
def add_comment(doc_id):
    if 'user' not in session:
        return jsonify({'error': 'Not authenticated'}), 401
    
    if doc_id not in documents:
        return jsonify({'error': 'Document not found'}), 404
    
    if documents[doc_id]['status'] != 'Under Review':
        return jsonify({'error': 'Document not available for comments'}), 400
    
    data = request.get_json()
    comment_text = data.get('comment', '').strip()
    
    if not comment_text:
        return jsonify({'error': 'Comment cannot be empty'}), 400
    
    if doc_id not in comments:
        comments[doc_id] = []
    
    comment = {
        'id': str(uuid.uuid4()),
        'user': session['user'],
        'comment': comment_text,
        'timestamp': datetime.now().isoformat()
    }
    
    comments[doc_id].append(comment)
    return jsonify({'success': True, 'comment': comment})

@app.route('/health', methods=['GET'])
def health():
    return jsonify({'status': 'healthy', 'message': 'Backend is running'})

@app.route('/api/test', methods=['GET', 'POST', 'OPTIONS'])
def test():
    return jsonify({
        'status': 'success', 
        'method': request.method,
        'message': 'API is working'
    })

if __name__ == '__main__':
    print("Starting Flask app on 0.0.0.0:5000")
    app.run(host='0.0.0.0', port=5000, debug=True)