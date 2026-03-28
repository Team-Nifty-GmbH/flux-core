# $nuxbe Global JS Helper

## Problem

The tall-datatables v2 migration removed `window.formatters` (client-side JS formatters). Flux-core and customer packages still reference `window.formatters.money()`, `window.formatters.date()`, etc. in ~40 Blade templates, chart traits, and Alpine directives. Additionally, several unrelated utilities (`window.parseNumber`, `window.fileSizeHumanReadable`, `window.$openDetailModal`) are scattered across `window.*` globals.

## Solution

A single `$nuxbe` Alpine magic property (also exposed as `window.$nuxbe`) that consolidates all formatting and utility functions. Follows the same pattern as TallStackUI's `$tsui`.

## API

### Formatting

All formatters live under `$nuxbe.format.*`:

```js
$nuxbe.format.money(value, options?)
// options: { colored: bool, currency: string }
// colored: wraps in <span> with green (positive) / red (negative)
// currency: defaults to <meta name="currency-code"> value
// returns: formatted string or HTML string (if colored)

$nuxbe.format.percentage(value)
// renders 0.15 as "15 %" using Intl.NumberFormat

$nuxbe.format.date(value)
// formats ISO date string using Intl.DateTimeFormat with user locale

$nuxbe.format.datetime(value)
// formats ISO datetime string with date + time

$nuxbe.format.relativeTime(timestamp)
// "2 hours ago" style using Intl.RelativeTimeFormat

$nuxbe.format.badge(text, color?)
// returns HTML <span> badge, color defaults to 'neutral'

$nuxbe.format.state(label, color)
// returns HTML state badge (colored dot + label)

$nuxbe.format.fileSize(bytes)
// "1.5 MB" style human-readable file size

$nuxbe.format.int(value)
// integer formatting with locale thousand separators

$nuxbe.format.float(value)
// float formatting with locale decimal/thousand separators
```

### Utilities

```js
$nuxbe.parseNumber(string)
// parses localized number string to float

$nuxbe.openDetailModal(url, hideNavigation?)
// opens the detail-modal iframe, defaults hideNavigation to true
```

## Architecture

### File Structure

```
resources/js/
  nuxbe.js              # Alpine plugin, registers $nuxbe magic + window.$nuxbe
  nuxbe/
    format.js           # all format.* methods
    utils.js            # parseNumber, openDetailModal
```

### Registration

`nuxbe.js` is an Alpine plugin imported in `alpine.js`:

```js
import nuxbe from './nuxbe.js';
Alpine.plugin(nuxbe);
```

The plugin registers:
1. `Alpine.magic('nuxbe', () => nuxbeInstance)` for Alpine contexts
2. `window.$nuxbe = nuxbeInstance` for non-Alpine contexts (charts, inline JS)

### Configuration

Reads from existing `<meta>` tags (already present in the app layout):
- `<meta name="currency-code">` for default currency
- Locale from `document.documentElement.lang`
- Timezone from `Intl.DateTimeFormat().resolvedOptions().timeZone`

No additional server-side configuration needed.

## Migration Map

### Replacements in flux-core

| Old | New |
|-----|-----|
| `window.formatters.money(x)` | `$nuxbe.format.money(x)` |
| `window.formatters.coloredMoney(x)` | `$nuxbe.format.money(x, {colored: true})` |
| `window.formatters.coloredMoney(x, symbol)` | `$nuxbe.format.money(x, {colored: true, currency: symbol})` |
| `window.formatters.percentage(x)` | `$nuxbe.format.percentage(x)` |
| `window.formatters.date(x)` | `$nuxbe.format.date(x)` |
| `window.formatters.datetime(x)` | `$nuxbe.format.datetime(x)` |
| `window.formatters.relativeTime(x)` | `$nuxbe.format.relativeTime(x)` |
| `window.formatters.badge(x, c)` | `$nuxbe.format.badge(x, c)` |
| `window.formatters.state(l, c)` | `$nuxbe.format.state(l, c)` |
| `window.formatters.int(x)` | `$nuxbe.format.int(x)` |
| `window.formatters.float(x)` | `$nuxbe.format.float(x)` |
| `window.parseNumber(x)` | `$nuxbe.parseNumber(x)` |
| `window.fileSizeHumanReadable(x)` | `$nuxbe.format.fileSize(x)` |
| `window.$openDetailModal(url)` | `$nuxbe.openDetailModal(url)` |
| `window.$promptValue(id)` | remove (evaluate if still needed) |
| `x-currency` directive | `x-html="$nuxbe.format.money(...)"` |
| `x-percentage` directive | `x-text="$nuxbe.format.percentage(...)"` |

### Affected flux-core files

**JS files:**
- `resources/js/app.js` - remove `window.parseNumber`, `window.fileSizeHumanReadable`, `window.$openDetailModal`
- `resources/js/components/alpine.js` - remove `x-currency`, `x-percentage` directives, add nuxbe plugin import

**PHP files:**
- `src/Traits/Livewire/Widget/MoneyChartFormattingTrait.php` - `window.formatters.money()` to `window.$nuxbe.format.money()`

**Blade templates (~40 files):**
- All `window.formatters.*` references in `resources/views/`

### Affected customer packages

Customer repos that use `window.formatters` in their Blade/JS files need the same replacements. These changes go into the existing `feature/laravel-13-support` PRs.

## What Gets Removed

- `window.formatters` global (was already deleted in tall-datatables v2)
- `window.parseNumber` function
- `window.fileSizeHumanReadable` function
- `window.$openDetailModal` function
- `window.$promptValue` function (verify usage first)
- `Alpine.directive('currency', ...)`
- `Alpine.directive('percentage', ...)`

## What Stays

- `window.filePond` (third-party, not ours)
- `window.$tallstackuiSelect` / `window.$tallstackuiToast` (TallStackUI internals)
- `window.axios`, `window.Pusher`, `window.Echo` (infrastructure, not nuxbe-specific)
- `window.nuxbeAppBridge` (mobile bridge, separate concern)
