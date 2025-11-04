import React, {useEffect, useState} from 'react';

export default function Comments({id, comments: initialComments = []}){
    const [comments, setComments] = useState(Array.isArray(initialComments) ? initialComments : []);
    const [loading, setLoading] = useState(initialComments && initialComments.length ? false : true);
    const [error, setError] = useState(null);

    useEffect(()=>{
        if (initialComments && initialComments.length) return;
        if (!id) {
            setError('No id provided');
            setLoading(false);
            return;
        }
        setLoading(true);
        fetch(`https://jsonplaceholder.typicode.com/posts/${id}/comments`)
            .then((r)=>{
                if (!r.ok) throw new Error('Network response was not ok');
                return r.json();
            })
            .then((data)=> setComments(Array.isArray(data) ? data : []))
            .catch((err)=> setError(err.message || String(err)))
            .finally(()=> setLoading(false));
    }, [id, initialComments]);

    if (loading) return (
        <div className="text-center py-4">
            <div className="spinner-border text-primary" role="status">
                <span className="visually-hidden">Loading...</span>
            </div>
        </div>
    );

    if (error) return (
        <div className="alert alert-danger" role="alert">Error in comments: {error}</div>
    );

    return (
        <div className="container py-3">
            <div className="card">
                <div className="card-header">
                    <h5 className="mb-0">Comments ({comments.length})</h5>
                </div>
                <div className="card-body">
                    {comments.length === 0 ? (
                        <div className="text-muted">No comments found.</div>
                    ) : (
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
                        <a href={`/albums/${id}`} className="btn btn-outline-secondary">Back to post</a>
                        <a href="/albums/list" className="btn btn-outline-primary ms-2">Back to list</a>
                    </div>
                </div>
            </div>
        </div>
    );
}
