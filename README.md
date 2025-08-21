# Collaborative Document Reviewer

A simple document collaboration platform with user authentication, document upload, and real-time commenting.

## Login credentials
admin / password123
reviewer1 / password123
reviewer2 / password123

## Features

- **User Authentication**: 3 hardcoded users (admin, reviewer1, reviewer2) with password "password123"
- **Document Upload**: Supports PDF and DOCX files
- **Document Status Management**: Pending Review, Under Review, Review Complete
- **Real-time Comments**: Comments visible to all users when document is "Under Review"
- **Simple UI**: Basic PHP frontend with JavaScript for API calls

## Architecture

- **Backend**: Python Flask REST API
- **Frontend**: PHP with vanilla JavaScript
- **Storage**: In-memory (files stored in container volume)
- **Containers**: 2 Docker containers (frontend, backend)

## Quick Start

### Local Development

1. Clone the repository:
```bash
git clone <your-repo-url>
cd collaborative-doc-reviewer
```

2. Create the directory structure:
```
.
├── backend/
│   ├── app.py
│   ├── Dockerfile
│   └── requirements.txt
├── frontend/
│   ├── index.php
│   └── Dockerfile
├── docker-compose.yml
└── .github/workflows/deploy.yml
```

3. Run with Docker Compose:
```bash
docker-compose up --build
```

4. Access the application at `http://localhost`

### AWS Deployment

#### Option 1: Elastic Beanstalk (Recommended for simplicity)

1. **Prepare for Beanstalk**:
   - Zip your entire project
   - Create Elastic Beanstalk application
   - Choose "Docker" platform

2. **Manual Deploy**:
   - Upload zip file to Beanstalk
   - Application will be available at your Beanstalk URL

#### Option 2: EC2 with Docker

1. Launch an EC2 instance (t2.micro for testing)
2. Install Docker and Docker Compose
3. Clone your repository
4. Run `docker-compose up -d`
5. Configure security group to allow ports 80 and 5000

## CI/CD Pipeline

The GitHub Actions workflow will:
1. Trigger on push to `prod` branch
2. Package the application
3. Deploy to AWS Elastic Beanstalk

### Setup GitHub Actions

1. Add these secrets to your GitHub repository:
   - `AWS_ACCESS_KEY_ID`
   - `AWS_SECRET_ACCESS_KEY`

2. Create Elastic Beanstalk application named "doc-reviewer"

3. Push to `prod` branch to trigger deployment

## Project Structure

```
collaborative-doc-reviewer/
├── backend/
│   ├── app.py              # Flask API server
│   ├── Dockerfile          # Backend container config
│   └── requirements.txt    # Python dependencies
├── frontend/
│   ├── index.php          # PHP frontend application
│   └── Dockerfile         # Frontend container config
├── docker-compose.yml     # Local development setup
├── .github/workflows/
│   └── deploy.yml         # CI/CD pipeline
└── README.md
```

## API Endpoints

- `POST /api/login` - User authentication
- `GET /api/check-auth` - Check login status
- `POST /api/upload` - Upload document
- `GET /api/documents` - List all documents
- `PUT /api/document/<id>/status` - Update document status
- `GET /api/document/<id>/comments` - Get document comments
- `POST /api/document/<id>/comments` - Add comment

## Usage

1. **Login**: Use admin/reviewer1/reviewer2 with password "password123"
2. **Upload**: Select PDF or DOCX file and upload
3. **Change Status**: Click "View" on a document and change status to "Under Review"
4. **Comment**: Add comments when document is "Under Review"
5. **Real-time**: Comments update automatically for all users

## Limitations

- In-memory storage (data lost on restart)
- No actual PDF/DOCX viewing (placeholder)
- Basic security (hardcoded users)
- No file size limits
- No input validation beyond basic checks

## For Production

To make this production-ready, consider:
- Database storage (PostgreSQL, MySQL)
- Proper authentication (JWT, OAuth)
- File storage (AWS S3)
- PDF/DOCX rendering libraries
- Input validation and sanitization
- HTTPS/SSL certificates
- Error logging and monitoring
- Load balancing
- Backup strategies