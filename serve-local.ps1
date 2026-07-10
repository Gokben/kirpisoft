$root = Split-Path -Parent $MyInvocation.MyCommand.Path
$listener = [System.Net.HttpListener]::new()
$listener.Prefixes.Add('http://127.0.0.1:8010/')
$listener.Start()

$types = @{
  '.html' = 'text/html; charset=utf-8'
  '.css' = 'text/css; charset=utf-8'
  '.js' = 'text/javascript; charset=utf-8'
  '.png' = 'image/png'
  '.jpg' = 'image/jpeg'
  '.jpeg' = 'image/jpeg'
  '.webp' = 'image/webp'
  '.svg' = 'image/svg+xml'
  '.woff' = 'font/woff'
  '.woff2' = 'font/woff2'
}

while ($listener.IsListening) {
  $context = $listener.GetContext()
  $requested = [Uri]::UnescapeDataString($context.Request.Url.AbsolutePath)
  if ($requested -eq '/') { $requested = '/index.html' }
  $file = [IO.Path]::GetFullPath((Join-Path $root $requested.TrimStart('/')))

  if (-not $file.StartsWith($root) -or -not (Test-Path -LiteralPath $file -PathType Leaf)) {
    $context.Response.StatusCode = 404
    $data = [Text.Encoding]::UTF8.GetBytes('Not found')
  } else {
    $extension = [IO.Path]::GetExtension($file).ToLowerInvariant()
    $context.Response.ContentType = if ($types[$extension]) { $types[$extension] } else { 'application/octet-stream' }
    $data = [IO.File]::ReadAllBytes($file)
  }

  $context.Response.ContentLength64 = $data.Length
  $context.Response.OutputStream.Write($data, 0, $data.Length)
  $context.Response.Close()
}
