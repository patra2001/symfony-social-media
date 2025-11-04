// Placeholder index so importing './react/controllers' succeeds.
// Add React controller exports or registrations here if needed.

// Re-export components from this directory so other code can import them
export { default as AlbumsList } from './AlbumsList.jsx';
// The Albums component lives under `react/components` (not `react/controllers`).
export { default as Albums } from '../components/Albums.jsx';