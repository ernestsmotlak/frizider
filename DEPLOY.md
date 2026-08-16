# Deploying to prod

App at `/var/www/frizider`. Run these as the deploy user, not as root.

Two vhosts, one checkout:

| Host | Root | Serves |
|---|---|---|
| `frizider.ernestsmotlak.dev` | `frontend/dist` | static SPA, no PHP |
| `api.ernestsmotlak.dev` | `public` | Laravel, `php8.4-fpm` |

## The commands, in order

```bash
cd /var/www/frizider
```

```bash
git pull
```

```bash
composer install --no-dev --optimize-autoloader
```

```bash
php artisan migrate --force
```

```bash
npm --prefix frontend ci
```

```bash
npm --prefix frontend run build
```

```bash
php artisan config:cache
```

```bash
php artisan route:cache
```

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
```

```bash
sudo supervisorctl restart frizider-worker:
```

```bash
sudo systemctl reload php8.4-fpm
```

Or as one line:

```bash
cd /var/www/frizider && git pull && composer install --no-dev --optimize-autoloader && php artisan migrate --force && npm --prefix frontend ci && npm --prefix frontend run build && php artisan config:cache && php artisan route:cache && sudo chown -R www-data:www-data storage bootstrap/cache && sudo supervisorctl restart frizider-worker: && sudo systemctl reload php8.4-fpm
```

## What each step is for

**`npm run build` is not optional.** `frontend/dist` is untracked, so `git pull` never brings it.
Skip this and nginx has no `index.html` to fall back to, `try_files` loops, and the site returns a
500 that never touches PHP. This is the one that bit on 2026-08-16.

**`config:cache` after touching `.env` or anything in `config/`.** Cached config ignores `.env`
entirely, so an edited key does nothing until this runs.

**`chown` because artisan runs as the deploy user but the app runs as `www-data`.** Any command
that writes to `storage/` or `bootstrap/cache/` leaves files the web user cannot then overwrite.

**Restart the worker on *every* deploy, even a prompt-only change.** `PromptRepository` is a
container singleton that memoises each prompt file on first read. In a long-running `queue:work`
process that cache lives as long as the process does — `cache:clear` does not touch it. An edited
`resources/prompts/*.md` reaches users only after the restart.

**`php8.4-fpm`, not 8.3.** That is what `fastcgi_pass` points at for the API vhost.

## Check it worked

```bash
ls -la /var/www/frizider/frontend/dist/index.html && sudo supervisorctl status frizider-worker: && curl -sI https://frizider.ernestsmotlak.dev | head -1
```

To confirm the worker picked up a changed prompt, run one generation and read the hash it recorded:

```bash
cd /var/www/frizider && php artisan tinker --execute="dump(\App\Models\UserAiRecipeLog::latest('id')->first()->request_meta['prompt_version'] ?? null);"
```

It must match the current files. If it still shows the old hash, the worker did not restart.

## When something breaks

```bash
sudo tail -n 40 /var/log/nginx/error.log && tail -n 40 /var/www/frizider/storage/logs/laravel.log
```

Nothing recent in `laravel.log` means PHP never ran — look at nginx and the static build, not the
app. Check timestamps before chasing anything; the last Laravel entry is often days old.
