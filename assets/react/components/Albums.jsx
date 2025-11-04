import React, {useState} from 'react';

export default function Albums({albums: initialAlbums = []}){
  const [albums, setAlbums] = useState(Array.isArray(initialAlbums) ? initialAlbums : []);
  const [filter, setFilter] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const filteredAlbums = albums.filter((album)=>
    String(album.title || '').toLowerCase().includes(filter.toLowerCase()) || String(album.userId || '').includes(filter)
);

return (
    <div className="container py-3">
      <div className="card">
        <div className="card-header d-flex align-items-center justify-content-between">
          <div>
            <h5 className="mb-0">Albums</h5>
          </div>
          <div>
            {loading ? (
              <div className="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></div>
            ) : (
              <span className="badge bg-secondary">{albums.length}</span>
            )}
          </div>
        </div>

        <div className="card-body">
          <div className="mb-3">
            <div className="input-group">
              <span className="input-group-text" id="search-addon">
                <i className="bi bi-search" aria-hidden="true"></i>
              </span>
              <input
                type="search"
                className="form-control"
                placeholder="Filter by title or user ID"
                aria-label="Filter albums"
                aria-describedby="search-addon"
                value={filter}
                onChange={(e) => setFilter(e.target.value)}
              />
              {filter && (
                <button className="btn btn-outline-secondary" type="button" onClick={() => setFilter('')}>
                  Clear
                </button>
              )}
            </div>
          </div>

          {error && (
            <div className="alert alert-danger" role="alert">
              Error loading albums: {error}
            </div>
          )}

          {!loading && !error && (
            <div className="list-group">
              {filteredAlbums.length > 0 ? (
                filteredAlbums.map((album) => (
                  <a key={album.id} href={`/albums/${album.id}`} className="list-group-item list-group-item-action d-flex justify-content-between align-items-start text-decoration-none text-reset">
                    <div className="ms-2 me-auto">
                      <div className="fw-bold">Title: {album.title}</div>
                      <div className="fw-bold">Description: {album.title}</div>
                      <small className="text-muted">Album ID: {album.id}</small>
                    </div>
                    <div>
                      <span className="badge bg-primary rounded-pill">User {album.userId}</span>
                    </div>
                  </a>
                ))
              ) : (
                <div className="text-muted">No albums match your filter.</div>
              )}
            </div>
          )}

          {loading && (
            <div className="text-center py-3">
              <div className="spinner-border text-primary" role="status">
                <span className="visually-hidden">Loading...</span>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}