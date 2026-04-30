# Content Types / Archive Desk

This file documents the v0.3 Panel content model. The canonical runtime map is
`contentTypeRegistry()` in `site/plugins/helpers/helpers.php`.

The current implementation is Kirby-native: the Site blueprint exposes an
"Icerik Uretimi / Arsiv Masasi" tab with one pages section per content type.
Future custom Panel areas should reuse the same parent/template/blueprint map.

| Type | Parent page | Template | Blueprint | Create label |
| --- | --- | --- | --- | --- |
| writing | fragmanlar | writing | pages/writing | Yeni yazi |
| book-review | marginalia | book-review | pages/book-review | Yeni kitap kaydi |
| film-review | perde | film-review | pages/film-review | Yeni film kaydi |
| playlist | rezonans | playlist | pages/playlist | Yeni playlist |
| photo-album | kadraj | photo-album | pages/photo-album | Yeni fotograf albumu |
| art-project | exhibit | art-project | pages/art-project | Yeni exhibit kaydi |

## Shared Field Contract

- Body content uses `body` where long-form content is needed. It is configured as
  Kirby blocks in new blueprints. Frontend rendering should use
  `contentRenderBody()` while old writer/text values may still exist.
- SEO fields are `seo_title`, `seo_description`, `og_image`.
- Primary media fields are `cover` for most types, `poster`/`backdrop` for film,
  and `gallery` for visual collections.
- Public tag fields are `tags` for writing, books, films, photo albums and art;
  playlists use `mood_tags`.
- Rating uses the shared 0-5 range field where applicable.
- Lightbox metadata lives on file blueprints, mainly `files/photo` and
  `files/art-image`.

## Frontend Fields By Type

- writing: `title`, `subtitle`, `summary`, `body`, `text`,
  `quote_highlight`, `quotes`, `writing_type`, `date`, `tags`, `cover`.
- book-review: `book_title`, `author`, `review_summary`, `body`, `date`,
  `rating`, `tags`, `cover`, `original_year`, `publisher`, `translator`,
  `page_count`, `isbn`.
- film-review: `film_title`, `original_title`, `tmdb_id`, `rating`, `date`,
  `director`, `release_year`, `runtime`, `countries`, `genres`,
  `short_review`, `body`, `tags`, `poster`, `backdrop`.
- playlist: `playlist_title`, `description`, `body`, `tracks`, `mood_tags`,
  `platform`, `spotify_url`, `track_count`, `duration`, `embed_code`, `cover`.
- photo-album: `subtitle`, `date`, `location`, `statement`, `gallery`, `cover`,
  `tags`, `camera`, `lens`, `film_stock`.
- art-project: `body`, `statement`, `gallery`, `cover`, `curator_note`, `year`,
  `production_duration`, `materials`, `paper`, `technique`, `dimensions`,
  `edition`, `inventory_code`, `status`, `tags`.
