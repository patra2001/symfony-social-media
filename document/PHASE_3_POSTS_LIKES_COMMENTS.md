# Phase 3: Posts, Comments, and Likes System

## Overview
Phase 3 implements the core social media functionality including post creation, commenting, and liking systems with full CRUD operations and access control.

## Completed Components

### 1. Form Types
#### PostType (`src/Form/PostType.php`)
- **Content field**: TextArea with validation (1-5000 characters)
- **Image field**: File upload with validation (max 5MB, image formats only)
- Handles both create and update operations
- Bootstrap 5 styling for consistency

#### CommentType (`src/Form/CommentType.php`)
- **Content field**: TextArea with validation (1-1000 characters)
- Compact form design for quick commenting
- Bootstrap form styling

### 2. PostController (`src/Controller/PostController.php`)
Handles all post-related operations:

#### Routes
| Route | Method | Permission | Description |
|-------|--------|-----------|-------------|
| `/post/create` | GET/POST | ROLE_USER | Create new post form and submission |
| `/post/feed` | GET | ROLE_USER | View paginated feed of posts |
| `/post/{id}/view` | GET/POST | ROLE_USER | View single post with comments |
| `/post/{id}/edit` | GET/POST | ROLE_USER | Edit post (author only) |
| `/post/{id}/delete` | POST | ROLE_USER | Delete post (author only) |
| `/post/{id}/like` | POST | ROLE_USER | Toggle like on post |
| `/post/comment/{id}/delete` | POST | ROLE_USER | Delete comment (author only) |

#### Key Features
- **File Upload Handling**: Images are stored with unique names in `public/uploads/posts/`
- **Pagination**: Feed displays 10 posts per page with navigation
- **CSRF Protection**: All POST requests protected with CSRF tokens
- **Authorization**: Security voters enforce access control
- **Timestamps**: Automatic tracking of creation and update times

### 3. Security Voters

#### PostVoter (`src/Security/Voter/PostVoter.php`)
- **EDIT**: Only post author can edit
- **DELETE**: Only post author can delete
- **VIEW**: Any authenticated user can view

#### CommentVoter (`src/Security/Voter/CommentVoter.php`)
- **DELETE**: Only comment author can delete
- **EDIT**: Only comment author can edit (preparation for future edit feature)

### 4. Templates

#### `templates/post/create.html.twig`
- Post creation form
- Content and image upload fields
- Validation error display
- Bootstrap card layout

#### `templates/post/edit.html.twig`
- Post edit form
- Displays current image
- Option to change/replace image
- Edit/cancel buttons

#### `templates/post/feed.html.twig`
- Main social feed view
- Quick post creation entry point
- Post cards showing:
  - Author profile image and name
  - Post timestamp
  - Post content and image (if present)
  - Like and comment counts
  - Quick like button
  - View & Comment link
- Pagination controls
- Edit/delete options for own posts
- Empty state messaging

#### `templates/post/view.html.twig`
- Single post detail view
- Full post information with edit/delete options
- Comments section with:
  - Comment creation form
  - List of all comments
  - Comment author info
  - Delete comment functionality (author only)
- Like button with like count
- Back to feed link

### 5. Database Configuration
Configuration in `config/services.yaml`:
```yaml
parameters:
    posts_directory: '%kernel.project_dir%/public/uploads/posts'
    profiles_directory: '%kernel.project_dir%/public/uploads/profiles'
```

### 6. Updated Components

#### Navigation (`templates/base.html.twig`)
Added navigation items for authenticated users:
- **Feed**: Link to post feed (`/post/feed`)
- **Create Post**: Link to create post page (`/post/create`)

### 7. Tests (`tests/Controller/PostControllerTest.php`)

#### Test Coverage
- ✅ Authentication requirements (redirect to login if not authenticated)
- ✅ Post creation with valid/invalid data
- ✅ Feed page loading
- ✅ Post viewing
- ✅ Post editing (author only)
- ✅ Post deletion (author only)
- ✅ Post liking
- ✅ Comment addition
- ✅ Authorization checks (non-authors cannot edit/delete)

#### Run Tests
```bash
php bin/console --env=test doctrine:database:create
php bin/console --env=test doctrine:migrations:migrate
php bin/phpunit tests/Controller/PostControllerTest.php
```

## File Structure

```
src/
├── Controller/
│   └── PostController.php       # Post CRUD and actions
├── Form/
│   ├── PostType.php             # Post form type
│   └── CommentType.php          # Comment form type
├── Security/
│   └── Voter/
│       ├── PostVoter.php        # Post access control
│       └── CommentVoter.php     # Comment access control

templates/
└── post/
    ├── create.html.twig         # Create post page
    ├── edit.html.twig           # Edit post page
    ├── feed.html.twig           # Post feed view
    └── view.html.twig           # Single post view

tests/
└── Controller/
    └── PostControllerTest.php   # Post controller tests

config/
├── services.yaml                # Upload directory parameters
└── packages/
    └── security.yaml            # Authorization/authentication
```

## Features in Detail

### Post Creation
1. User clicks "Create Post" in navigation
2. Form displays with content and optional image field
3. Content required (1-5000 chars), image optional
4. Image validation: max 5MB, only image formats
5. On submit, post created with current user as author
6. Timestamps auto-set to current moment
7. Redirect to feed with success message

### Post Feed
- Paginated view of all posts (newest first)
- Each post shows:
  - Author profile picture and name (clickable link)
  - Post creation timestamp
  - Post content text
  - Post image (if attached)
  - Like count and comment count
  - Like button (toggles/untogles)
  - View & Comment button
  - Author can see Edit/Delete options
- Pagination with page numbers

### Post Interaction
- **Like**: User clicks heart icon to like/unlike post
- **Comment**: User visits post detail page
  - Comment form at top with user's avatar
  - List of all comments below
  - Each comment shows author, timestamp, content
  - Authors can delete their own comments

### Access Control
- **View**: Any authenticated user
- **Edit/Delete**: Only author can perform
- **Unauthorized attempts**: 403 Forbidden response

### Image Storage
- Files stored in `public/uploads/posts/`
- Unique filenames: `{slug}-{uniqid}.{ext}`
- Referenced in templates: `asset('uploads/posts/' ~ post.image)`

## API Integration Points

### LikeRepository
Used to check if user already liked a post before creating/removing like:
```php
$existingLike = $likeRepository->findOneBy([
    'post' => $post,
    'user' => $this->getUser(),
]);
```

### PostRepository
- `findRecentPosts($page, $limit)`: Get paginated recent posts
- `findByAuthor($author, $page, $limit)`: Get user's posts (for profile page in Phase 4)
- `findPostsFromFollowing($user, $page, $limit)`: Get feed from followed users (Phase 4)

### EntityManager
- Persist and flush new posts/comments/likes
- Remove deleted posts/comments
- Cascade deletes handled automatically

## Configuration Notes

### File Uploads
1. Create directories:
   ```bash
   mkdir -p public/uploads/posts
   mkdir -p public/uploads/profiles
   chmod 755 public/uploads/posts
   chmod 755 public/uploads/profiles
   ```

2. Configure parameters in `config/services.yaml`

3. Symfony's String Slugger used for filename sanitization

### CSRF Protection
All POST requests require valid CSRF token:
- Token ID includes entity ID (e.g., `like12`, `delete34`)
- Tokens passed in hidden form fields
- Verified in controller before action

### Cascade Delete
Configured in Post entity:
- Deleting post automatically deletes all related likes
- Deleting post automatically deletes all related comments

## Future Enhancements

### Phase 4: Profiles & Follow System
- User profile pages showing posts
- Follow/unfollow functionality
- Personalized feed showing only followed users
- User statistics (follower count, post count)

### Phase 5: Advanced Features
- Post search and filtering
- Hashtags and mentions
- Notifications system
- Real-time updates (WebSockets)
- Post sharing
- Media gallery

## Security Considerations

1. **CSRF Protection**: All state-changing requests require valid tokens
2. **Authorization**: Voters ensure users only modify their own content
3. **File Upload**: Validates file type, size, and stores with unique names
4. **Input Validation**: All form fields validated at controller and entity level
5. **SQL Injection**: Doctrine ORM parameterizes all queries
6. **XSS Prevention**: Twig escapes all output by default

## Troubleshooting

### Image Upload Not Working
1. Check `public/uploads/posts/` directory exists and is writable
2. Verify `posts_directory` parameter in `config/services.yaml`
3. Check file size limit in php.ini

### Posts Not Appearing in Feed
1. Verify posts exist in database: `SELECT * FROM post;`
2. Check pagination - may be on empty page
3. Ensure user is authenticated

### CSRF Token Errors
1. Clear browser cookies/session
2. Verify form includes `{{ csrf_field() }}` or equivalent
3. Check security configuration

### Access Denied (403)
1. Verify you are the post author
2. Check voters are registered in service container
3. Verify `#[IsGranted('ROLE_USER')]` attribute on controller action

## Performance Considerations

1. **Pagination**: Feed uses pagination (10 per page) to avoid loading all posts
2. **Indexes**: Database indexes on author_id, post_id, user_id for fast lookups
3. **Lazy Loading**: Comment and like collections loaded only when accessed
4. **Image Optimization**: Consider adding image resizing for thumbnails (Phase 5)

## Deployment Checklist

- [ ] Create upload directories with proper permissions
- [ ] Set `posts_directory` and `profiles_directory` parameters
- [ ] Run database migrations: `php bin/console doctrine:migrations:migrate`
- [ ] Run tests: `php bin/phpunit`
- [ ] Configure file upload limits in php.ini (post_max_size, upload_max_filesize)
- [ ] Set up CDN or backup storage for user-uploaded files
- [ ] Implement virus scanning for uploaded files (optional)