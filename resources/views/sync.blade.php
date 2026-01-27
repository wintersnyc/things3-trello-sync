<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Things ↔ Trello Sync</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      font-family: system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
      margin: 24px;
      max-width: 1200px;
    }
    button {
      padding: 10px 16px;
      font-size: 16px;
      cursor: pointer;
    }
    pre {
      background: #f6f6f6;
      padding: 12px;
      border-radius: 8px;
      overflow: auto;
    }
  </style>
</head>
<body>

  <h1>Things ↔ Trello Sync</h1>

  <form method="POST" action="/sync">
    @csrf
   
    <p>
      <label>
        Token:
        <input name="token" value="{{ $token ?? '' }}" style="padding:8px; width: 360px;" />
      </label> 
    </p>

    <p>
      <label>
      <input type="checkbox" name="dry_run" value="1" {{ !empty($dry_run) ? 'checked' : '' }}>
      Dry run (do not change Things or Trello)
      </label>
    </p>


    <button type="submit">Sync Now</button>
  </form>

  @if (!empty($output))
    <h2>Output</h2>
    <pre>{{ $output }}</pre>
  @endif

</body>
</html>