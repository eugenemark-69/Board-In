# Fetch demo images for Board-In into assets/images/
# Usage: run this script in PowerShell (Windows)

$destDir = "C:\xampp\htdocs\board-in\assets\images"
if (!(Test-Path $destDir)) { New-Item -ItemType Directory -Path $destDir -Force }

$images = @(
    @{url="https://source.unsplash.com/900x600/?boarding-house"; file="sample1.jpg"},
    @{url="https://source.unsplash.com/900x600/?student-house"; file="sample2.jpg"},
    @{url="https://source.unsplash.com/900x600/?dorm-room"; file="sample3.jpg"},
    @{url="https://source.unsplash.com/900x600/?studio-apartment"; file="sample4.jpg"}
)

foreach ($img in $images) {
    $out = Join-Path $destDir $img.file
    Write-Host "Downloading $($img.url) -> $out"
    try {
        Invoke-WebRequest -Uri $img.url -OutFile $out -UseBasicParsing -ErrorAction Stop
    } catch {
        Write-Host "Failed to download $($img.url): $_" -ForegroundColor Yellow
    }
}

Write-Host "Done. Images saved to $destDir" -ForegroundColor Green
