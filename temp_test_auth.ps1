$s = New-Object Microsoft.PowerShell.Commands.WebRequestSession
Invoke-WebRequest -Uri 'http://localhost/thrift_pos/login' -Method Post -Body @{ username='staff'; password='staff123' } -WebSession $s -MaximumRedirection 3 | Out-Null
Invoke-WebRequest -Uri 'http://localhost/thrift_pos/pos' -WebSession $s -UseBasicParsing | Select-Object StatusCode, StatusDescription
Invoke-WebRequest -Uri 'http://localhost/thrift_pos/api/items' -WebSession $s -UseBasicParsing | Select-Object StatusCode, StatusDescription
