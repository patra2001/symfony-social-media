# Phase 3: Quick Start Guide

## 🚀 Getting Started

### 1. Clear Cache
```bash
php bin/console cache:clear
```

### 2. Verify Upload Directories
The following directories should exist:
- `public/uploads/posts/`
- `public/uploads/profiles/`

They've been created automatically. Verify with:
```bash
ls -la public/uploads/
```

### 3. Test the Application

#### Start the Development Server
```bash
php bin/console server:run
# or
symfony server:start
```

#### Run Tests
```bash
# Create test database
php bin/console --env=test doctrine:database:create

# Run migrations for test database
php bin/console --env=test doctrine:migrations:migrate

# Run Phase 3 tests
php bin/phpunit tests/Controller/PostControllerTest.php
```

## 📝 User Workflows

### Create a Post
1. Register and login if not already done
2. Click **"Create Post"** in navigation bar
3. Enter post content (1-5000 characters)
4. (Optional) Upload an image (5MB max, image formats)
5. Click **"Post"** button
6. You're redirected to feed with success message

### View Your Feed
1. Click **"Feed"** in navigation bar
2. See posts from all users in reverse chronological order
3. Use pagination to browse older posts (10 per page)

### Like a Post
1. On any post in the feed, click the **heart icon**
2. Heart turns red and like count increases
3. Click again to unlike

### Comment on a Post
1. Click **"View & Comment"** on a post card
2. Or click anywhere on the post card in detail view
3. Scroll to **"Comments"** section
4. Type your comment (1-1000 characters)
5. Click **"Comment"** button
6. Comment appears immediately

### Edit Your Post
1. Click the **three dots (•••)** on your own post
2. Select **"Edit"**
3. Modify content and/or image
4. Click **"Update Post"**
5. You're redirected back to post view

### Delete Your Post
1. Click the **three dots (•••)** on your own post
2. Select **"Delete"**
3. Confirm deletion
4. Post is removed and you're redirected to feed

### Delete Your Comment
1. Hover over or view your comment on a post
2. Click **"Delete"** link
3. Confirm deletion
4. Comment is removed

## 🔒 Important Notes

### Access Control
- ✅ You can view any post on the feed
- ✅ You can comment on any post
- ✅ You can like any post
- ❌ You can ONLY edit/delete your OWN posts
- ❌ You can ONLY delete your OWN comments

### Image Uploads
- Maximum file size: 5MB
- Allowed formats: JPEG, PNG, GIF, WebP
- Files are automatically renamed and stored securely
- Images appear below post content

## 🧪 Testing

### Automated Tests
Run the full test suite:
```bash
php bin/phpunit tests/Controller/PostControllerTest.php
```

Expected output: **13 tests passed** ✓

### Manual Testing Checklist
- [ ] Can create a post without image
- [ ] Can create a post with image
- [ ] Can see posts in feed
- [ ] Can like/unlike a post
- [ ] Can add comment to post
- [ ] Can delete own comment
- [ ] Can edit own post
- [ ] Can delete own post
- [ ] Cannot edit other user's post (403)
- [ ] Cannot delete other user's post (403)
- [ ] Pagination works (create 10+ posts)
- [ ] Invalid image rejected (>5MB or wrong format)

## 📊 Database

### Check Created Data
```sql
-- View all posts
SELECT * FROM post;

-- View all comments
SELECT * FROM comment;

-- View all likes
SELECT * FROM like;

-- View likes count per post
SELECT p.id, p.content, COUNT(l.id) as likes FROM post p LEFT JOIN like l ON p.id = l.post_id GROUP BY p.id;
```

## 🐛 Troubleshooting

### Post Not Appearing
```bash
# Check if post exists
php bin/console doctrine:query:sql "SELECT * FROM post LIMIT 5"

# Check if pagination is set correctly
# Try page 1: http://localhost:8000/post/feed?page=1
```

### Image Upload Failing
```bash
# Check directory exists
ls -la public/uploads/posts/

# Check permissions (should be writable)
chmod 755 public/uploads/posts/

# Check php.ini settings
php -i | grep -E "upload_max_filesize|post_max_size"
```

### CSRF Token Error
```bash
# Clear your browser session
# Or try in a new incognito window
```

### 403 Forbidden on Edit
- Make sure you're the post author
- Check you're logged in with the correct user

## 📱 Navigation Changes

The navigation bar now includes (when logged in):
```
SocialMedia | Feed | Create Post | Dashboard | Username ▼
```

## 🔄 What's Included in Phase 3

### Controllers
- ✅ PostController (7 routes)

### Forms
- ✅ PostType (content + image)
- ✅ CommentType (content only)

### Features
- ✅ Create posts with optional images
- ✅ Edit own posts
- ✅ Delete own posts
- ✅ Like/unlike posts
- ✅ View post feed with pagination
- ✅ View detailed post with all comments
- ✅ Add comments to posts
- ✅ Delete own comments
- ✅ Security/access control
- ✅ CSRF protection

### Templates
- ✅ Post creation page
- ✅ Post editing page
- ✅ Post feed (paginated)
- ✅ Post detail with comments

## 🔮 What's Coming in Phase 4

- [ ] User profiles
- [ ] Follow/unfollow system
- [ ] Personalized feed (only followed users)
- [ ] User statistics (posts, followers, following)
- [ ] Profile customization

## 💡 Tips & Tricks

### Performance
- Feed loads 10 posts per page for better performance
- Click "Next" in pagination to load older posts

### Privacy
- Posts are visible to all authenticated users
- To make posts private (Phase 5), you'd need to add privacy settings

### Engagement
- Like posts to show support
- Comment to start conversations
- Follow users to see their posts first (Phase 4)

## 📞 Need Help?

1. Check the detailed documentation: `PHASE_3_POSTS_LIKES_COMMENTS.md`
2. Run tests to verify functionality
3. Check browser console for JavaScript errors
4. Check server logs: `var/log/dev.log`

## 🎯 Next Steps

1. ✅ Create a test post
2. ✅ Add a comment to your post
3. ✅ Like another user's post (if available)
4. ✅ Modify and delete your post
5. ✅ Run the automated tests
6. → Proceed to Phase 4: Profiles & Follow System

---

**Phase 3 Status**: ✅ Complete and Ready for Use!