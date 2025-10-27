# Phase 1: User & Authentication Setup - Complete ✅

## Overview
Phase 1 establishes the foundation for the social media platform by creating the User entity, authentication configuration, and placeholder entities for Posts, Comments, and Likes.

## What Was Created

### 1. **User Entity** (`src/Entity/User.php`)
Complete user model implementing `UserInterface` and `PasswordAuthenticatedUserInterface`.

**Key Features:**
- Email-based authentication
- Password hashing support
- Role-based access control (ROLE_USER, ROLE_ADMIN, etc.)
- Profile management (firstName, lastName, bio, profileImage)
- Email verification system (token-based)
- Account status tracking (active/banned)
- Timestamps (createdAt, updatedAt)
- Relations with Posts, Likes, Comments, and Followers

**Properties:**
```php
- id: integer (primary key)
- email: string (unique)
- password: string (hashed)
- firstName: string
- lastName: string
- bio: text (nullable)
- profileImage: string (nullable)
- roles: array (default: ['ROLE_USER'])
- isEmailVerified: boolean (default: false)
- emailVerificationToken: string (nullable)
- emailVerificationSentAt: datetime (nullable)
- isActive: boolean (default: true)
- isBanned: boolean (default: false)
- createdAt: datetime
- updatedAt: datetime
```

**Helper Methods:**
- `getFullName()`: Returns "firstName lastName"
- `isFollowing(User)`: Check if following another user
- `isFollowedBy(User)`: Check if followed by another user

### 2. **Post Entity** (`src/Entity/Post.php`)
User-generated content with likes and comments.

**Properties:**
- id, content, image, author (User), createdAt, updatedAt

**Relations:**
- One-to-Many with Likes
- One-to-Many with Comments

**Helper Methods:**
- `getLikeCount()`: Get total likes
- `getCommentCount()`: Get total comments
- `isLikedBy(User)`: Check if user liked this post

### 3. **Like Entity** (`src/Entity/Like.php`)
Represents a user's like on a post.

**Properties:**
- id, user (User), post (Post), createdAt

**Constraints:**
- Unique constraint on (user_id, post_id) - one like per user per post

### 4. **Comment Entity** (`src/Entity/Comment.php`)
Comments on posts.

**Properties:**
- id, content, author (User), post (Post), createdAt, updatedAt

### 5. **Repositories**
Each entity has a dedicated repository class:

- **UserRepository**: 
  - `findByEmailVerificationToken()`: Find user by email token
  - `findActiveUsers()`: Get active users
  - `searchUsers()`: Search users by name/email

- **PostRepository**:
  - `findRecentPosts()`: Paginated recent posts
  - `findByAuthor()`: Posts by specific author
  - `findPostsFromFollowing()`: Posts from followed users

- **LikeRepository**:
  - `findUserPostLike()`: Check if user liked post
  - `countLikes()`: Count likes on a post
  - `findUsersWhoLiked()`: Get users who liked a post

- **CommentRepository**:
  - `findByPost()`: Get comments for a post (paginated)
  - `countComments()`: Count comments on a post

### 6. **Security Configuration** (`config/packages/security.yaml`)
Updated to use the new User entity:
- Password hasher: auto (bcrypt)
- User provider: entity-based on `App\Entity\User`
- Login path: `/login`
- Username parameter: `email`
- CSRF protection: enabled

## Database Schema

### Tables Created:
1. **user** - Main user table with all profile info
2. **post** - User posts/content
3. **like** - Post likes (unique per user per post)
4. **comment** - Post comments
5. **user_followers** - Many-to-many follower relationship

### Relations:
```
User
├── 1:N → Post (author)
├── 1:N → Like (user)
├── 1:N → Comment (author)
├── M:N → User (followers)
└── M:N → User (following) [inverse of followers]

Post
├── M:1 ← User (author)
├── 1:N → Like
└── 1:N → Comment

Like
├── M:1 → User
└── M:1 → Post

Comment
├── M:1 → User (author)
└── M:1 → Post
```

## Next Steps (Phase 2)

In Phase 2, we will implement:
1. **User Registration** - RegisterController, RegisterFormType
2. **Login System** - LoginController, login templates
3. **Email Verification** - EmailVerificationService, verification templates
4. **User Profile** - ProfileController, profile management

## Testing the Phase 1 Setup

### 1. Check if entities are recognized:
```bash
php bin/console doctrine:schema:validate
```

### 2. Create a test user (manually via database or fixtures):
```php
$user = new User();
$user->setEmail('test@example.com');
$user->setPassword('hashed_password');
$user->setFirstName('John');
$user->setLastName('Doe');
$this->entityManager->persist($user);
$this->entityManager->flush();
```

### 3. Verify the User entity implements SecurityUser:
```php
// User must implement UserInterface and PasswordAuthenticatedUserInterface
$user = $userRepository->findOneBy(['email' => 'test@example.com']);
echo $user->getUserIdentifier(); // Should output the email
```

## Files Created/Modified

### Created:
- `src/Entity/User.php`
- `src/Entity/Post.php`
- `src/Entity/Like.php`
- `src/Entity/Comment.php`
- `src/Repository/UserRepository.php`
- `src/Repository/PostRepository.php`
- `src/Repository/LikeRepository.php`
- `src/Repository/CommentRepository.php`
- `migrations/Version20251021054644.php` (Database migration)

### Modified:
- `config/packages/security.yaml` (Removed deprecated csrf_token_generator)

## Security Notes

1. **Password Security**: User passwords must be hashed using Symfony's PasswordHasher before saving.
2. **Email Verification Tokens**: Should be randomly generated, unique, and have expiration logic (implemented in Phase 2).
3. **CSRF Protection**: Enabled for form_login in security.yaml.
4. **Access Control**: Configure in Phase 5 for role-based access.

## Configuration Summary

**Entity Doctrine Mapping:**
- Using PHP 8 attributes for mapping
- Auto-incrementing primary keys
- Timestamps managed in entity constructors
- Cascade delete for data integrity

**Repository Pattern:**
- Each entity has custom query methods
- DQL queries for complex filters
- Pagination support built-in

---

**Status**: ✅ Phase 1 Complete - Ready for Phase 2: User Registration, Login & Email Verification