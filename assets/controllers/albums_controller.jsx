import { Controller } from '@hotwired/stimulus';
import React, { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';

function AlbumsApp({ apiUrl }) {
    const [albums, setAlbums] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [query, setQuery] = useState('');

    useEffect(() => {
        let mounted = true;
        setLoading(true);
        fetch(apiUrl)
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Failed to load albums');
                }
                return response.json();
            })
            .then((data) => {
                if (mounted) {
                    setAlbums(Array.isArray(data) ? data : []);
                    setLoading(false);
                }
            })
            .catch((err) => {
                if (mounted) {
                    setError(err.message || String(err));
                    setLoading(false);
                }
            });

        return () => {
            mounted = false;
        };
    }, [apiUrl]);

    const filtered = useMemo(() => {
        const normalized = query.trim().toLowerCase();
        if (!normalized) {
            return albums;
        }
        return albums.filter((album) => {
            const title = String(album.title || '').toLowerCase();
            const userId = String(album.userId ?? '');
            return title.includes(normalized) || userId.includes(normalized);
        });
    }, [albums, query]);

    return (
        <div className="container py-3">
            <div className="row mb-3">
                <div className="col-md-8">
                    <input
                        className="form-control"
                        value={query}
                        onChange={(event) => setQuery(event.target.value)}
                        placeholder="Filter by title or user ID"
                        aria-label="Filter albums"
                    />
                </div>
            </div>
            {loading && <div className="text-muted">Loading albums...</div>}
            {error && <div className="text-danger">Error: {error}</div>}
            {!loading && !error && (
                filtered.length > 0 ? (
                    <div className="list-group">
                        {filtered.map((album) => (
                            <div key={album.id} className="list-group-item">
                                <div className="d-flex justify-content-between">
                                    <div>
                                        <strong>{album.title}</strong>
                                        <div className="text-muted small">
                                            Album ID: {album.id} — User ID: {album.userId}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="text-muted">No albums match your filter.</div>
                )
            )}
        </div>
    );
}

export default class extends Controller {
    connect() {
        const apiUrl = this.element.getAttribute('data-api-url') ?? 'https://jsonplaceholder.typicode.com/albums';
        this.rootEl = document.createElement('div');
        this.element.appendChild(this.rootEl);
        this.root = createRoot(this.rootEl);
        this.root.render(<AlbumsApp apiUrl={apiUrl} />);
    }

    disconnect() {
        if (this.root) {
            this.root.unmount();
            this.root = null;
        }
        if (this.rootEl?.parentNode) {
            this.rootEl.parentNode.removeChild(this.rootEl);
            this.rootEl = null;
        }
    }
}
