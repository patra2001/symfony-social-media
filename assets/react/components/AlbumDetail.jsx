import React, {useEffect, useState} from 'react';

export default function AlbumDetail({id, post: initialPost = null}){
    // If the server passes a `post` prop, use it as initial data and skip fetching.
    const [post, setPost] = useState(initialPost);
    const [loading, setLoading] = useState(initialPost ? false : true);
    const [error, setError] = useState(null);

    const [comments, setComments] = useState([]);
    const [showComments, setShowComments] = useState(false);
    const [commentsLoading, setCommentsLoading] = useState(false);
    const [commentsError, setCommentsError] = useState(null);

    // useEffect(()=>{
    //     if (initialPost) {
    //         return;
    //     }
    //     if (!id) {
    //         setError('No id provided');
    //         setLoading(false);
    //         return;
    //     }
    //     setLoading(true);
    //     fetch(`https://jsonplaceholder.typicode.com/posts/${id}`)
    //         .then((r)=>{
    //             if (!r.ok) throw new Error('Network response was not ok');
    //             return r.json();
    //         })
    //         .then((data)=> setPost(data))
    //         .catch((err)=> setError(err.message || String(err)))
    //         .finally(()=> setLoading(false));
    // }, [id, initialPost]);

      const toggleComments = () => {
    if (!showComments && comments.length === 0) {
      setCommentsLoading(true);
      fetch(`https://jsonplaceholder.typicode.com/posts/${id}/comments`)
        .then((r) => {
          if (!r.ok) throw new Error("Failed to load comments");
          return r.json();
        })
        .then((data) => setComments(data))
        .catch((err) => setCommentsError(err.message || String(err)))
        .finally(() => setCommentsLoading(false));
    }
    setShowComments(!showComments);
  };

    if (loading) return (
        <div className="text-center py-5">
            <div className="spinner-border text-primary" role="status">
                <span className="visually-hidden">Loading...</span>
            </div>
        </div>
    );

    if (error) return (
        <div className="alert alert-danger" role="alert">Error loading post: {error}</div>
    );

    if (!post) return <div className="text-muted">No data available.</div>;

    // return (
    //     <div className="container py-4">
    //         <div className="card">
    //             <div className="card-body">
    //                 <h3 className="card-title">{post.title}</h3>
    //                 <h6 className="text-muted">Post ID: {post.id} <br></br> User {post.userId}</h6>
    //                 <p className="card-text mt-3">{post.body}</p>
    //                 <a href={`/albums/${post.id}/comments`} className="btn btn-outline-secondary">Show All Comments</a>
    //             </div>
    //         </div>
    //     </div>
    // );

    return (
    <div className="container py-4">
      <div className="card">
        <div className="card-body">
          <h3 className="card-title">{post.title}</h3>
          <h6 className="text-muted">
            Post ID: {post.id} <br /> User {post.userId}
          </h6>
          <p className="card-text mt-3">{post.body}</p>
          <button
            className="btn btn-outline-secondary"
            onClick={toggleComments}
            disabled={commentsLoading}
          >
            {commentsLoading
              ? "Loading Comments..."
              : showComments
              ? "Hide All Comments"
              : "Show All Comments"}
          </button>
        </div>
      </div>

      {/* Comments Section */}
      {showComments && (
        <div className="mt-4">
          <h5>Comments</h5>
          {commentsError && (
            <div className="alert alert-danger">{commentsError}</div>
          )}
          {commentsLoading && (
            <div className="text-center py-3">
              <div className="spinner-border text-secondary" role="status">
                <span className="visually-hidden">Loading...</span>
              </div>
            </div>
          )}
          {!commentsLoading && comments.length > 0 && (
              <div className="list-group">
                {comments.map((c) => (
                  <div key={c.id} className="list-group-item">
                    <div className="d-flex w-100 justify-content-between">
                      <h6 className="mb-1">{c.name}</h6>
                      <small className="text-muted">{c.email}</small>
                    </div>
                    <p className="mb-1">{c.body}</p>
                  </div>
                ))}
              </div>
            )}
            <div className="mt-3">
                {/* <a href={`/albums/${id}`} className="btn btn-outline-secondary">Back to post</a> */}
                <a href="/albums/list" className="btn btn-outline-primary ms-2">Back to list</a>
            </div>
        </div>
      )}
    </div>
  );
}
