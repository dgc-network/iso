// ssl_cert.h
#ifndef SSL_CERT_H
#define SSL_CERT_H

static const char serverCert[] PROGMEM = R"EOF(
-----BEGIN CERTIFICATE-----
MIIDPjCCAiYCCQC64kHqEafvWDANBgkqhkiG9w0BAQsFADBhMQswCQYDVQQGEwJU
VzEPMA0GA1UEBwwGVGFpcGVpMRcwFQYDVQQKDA5pc28taGVscGVyLmNvbTEoMCYG
CSqGSIb3DQEJARYZcm92ZXIuY2hlbkBpc28taGVscGVyLmNvbTAeFw0yNDA2MjQw
MDUwNTVaFw0yNTA2MjQwMDUwNTVaMGExCzAJBgNVBAYTAlRXMQ8wDQYDVQQHDAZU
YWlwZWkxFzAVBgNVBAoMDmlzby1oZWxwZXIuY29tMSgwJgYJKoZIhvcNAQkBFhly
b3Zlci5jaGVuQGlzby1oZWxwZXIuY29tMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8A
MIIBCgKCAQEA1eY+zfUfgA4yK8Tl2ispNXHLjkHTYzFQVhDkZ2Dm6G69qTQe7KWA
fnvG/UOoZfhTxsvbjWOg95sy6fTzH178Bhj0TwTtvsG6ofpKwhi6whThtkz6cKI1
cXNihYLL8ol15M4mjX3N2Ou38O5Qr+0/IvUgvwAuNlhEH9D+apZGXP8OUtR62dQB
CANIS8IJNgeKUeRUijLECUg7oK1sx5A3f1w7F0oX14jiizQ9OCOzxDg6qQAuDrvk
poFjZipbdXc0vDVUy9JtRJmh4+hiWimPFetmAvk+hFoCxiOtHYkNvY2mhP/fayTo
TMDMnh+Uc9FyPVXydGtzWDA3l4vvt4izCwIDAQABMA0GCSqGSIb3DQEBCwUAA4IB
AQDUX+BJfFJ63lX+MrEYQhJ/x1KgR3aoFQSF6OCdwvdb01hegX7mdhMWacOn9TN5
pfHOQ5xB3eCvpCptbaxJE4Pfyx0Iqolhk/IOcPx5Qm7Sm9XEYsLUFv53a6WhkC/y
uTI5qWbH8zZoOaZfRrjqusZR8SRUmpd/c024k1AfC0ftF6lw+0I5VpnRYWXcxmIF
Tj5ZkjNFJb3ag7qIAcE5VBGu/z+S3l0d2kbdiy2bojHvf7Ux6jI26/BljRa8sM4l
pE/FuscmZcQVcuASCkcj0FWPzZOjqDxp6jgdVYRrp1RYuWhcq8oGrCOA0Q6Tzcg+
L1nplVkd+kebBbrKq1s7+L/o
-----END CERTIFICATE-----
)EOF";

static const char serverKey[] PROGMEM = R"EOF(
-----BEGIN RSA PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDV5j7N9R+ADjIr
xOXaKyk1ccuOQdNjMVBWEORnYObobr2pNB7spYB+e8b9Q6hl+FPGy9uNY6D3mzLp
9PMfXvwGGPRPBO2+wbqh+krCGLrCFOG2TPpwojVxc2KFgsvyiXXkziaNfc3Y67fw
7lCv7T8i9SC/AC42WEQf0P5qlkZc/w5S1HrZ1AEIA0hLwgk2B4pR5FSKMsQJSDug
rWzHkDd/XDsXShfXiOKLND04I7PEODqpAC4Ou+SmgWNmKlt1dzS8NVTL0m1EmaHj
6GJaKY8V62YC+T6EWgLGI60diQ29jaaE/99rJOhMwMyeH5Rz0XI9VfJ0a3NYMDeX
i++3iLMLAgMBAAECggEBAMhuAXe4MxYpNzXwQHlYbDraMKVSZSPTd7XOClgcYwJ1
kl7Uqg2RX4Alt+Zl2YwDT20+ZLJuIs0hrmmvz4kb1ghYpcTNl4FhwuI4yIhr58Qp
EP9jzmBwfqeaOT1lvHm9+Ky3x0RaF+MlrayjgxP2uWymSVLwdl0SBHPTwDJcMf68
gQ40t+XXHKIGTg/hjDsAzRrtgDkSeprwAmFoaq5vojhFCOA9BLnwqIrGDXwjrv5z
zJSX2rk3kPiAhADijL8WK9XpxC+07vz4wlTnY3uDnQ2Rkel0dh9pGKXScC+8sbwO
U05JHctzu8N3ehHlbsFS5p4PVaa85SQ1i98AUHOzF2ECgYEA+/yDUliC+8VHGPIH
a6wg2rlvlrAMAr4FhuI8X7oONQSEBX8W5P1aUcZuYqjQ34iPQ5O0je0loIwXHyw4
5rMqhESgFLIi5iiYT/0WczIVQGm1RCsRHlRak9e0tL9nhxUZsKc3VjZOrf+yhKSZ
JZUkcFlaqB3qxRcAp10FnTHHf1kCgYEA2U5uR8Sg5BK1LmHxZERJrw4Cyh00ycrP
3HL1st8xN65l2UcJKm+S9mdqndAPAgNe8yEux+vRXn2kBIGNBFSiRNj5UMTdVKB2
adCRlaJTfF12SmrRjuI88kqsirjFhEDzwkFYzDhXK0FckB7yCo8yX2yD3/xU05F4
3ZKKug5qPQMCgYEA4bDWxcg0+N9GwJb2AoAWpRB1W5euCj67oIW84Vg5JU84F9wu
W0Z3wMpjT8Y33h3ngvUmP//pNZSDmQ34oNhT+kekwSSTkWVGlk3t75sp4ZlMGxQf
rvSKc+Q9G65bQWeqrMf4DiVx7vIXqCnsaPbdviqpwe2ZupDRqxTr1FEjh3ECgYBI
67DGFI2I+14hOmyuoNu5CpKVEEfuj0hBSbJ5W7xAWx2CU/wXaWl/liSI4JCotEjg
fXodTFztwGuRt4eCtIPfZpADMoyzIUWbLIouFFK/oP1Y6492yyR4ieZshqSBROqH
fTY3EZDuyvgsxLWkJXlZ3ChEuYAdnutYLxyuYrfz0QKBgCTjFiJnK72sJxzwleC9
Una4X9uwxqrNGw+ckprbhwa7j5ZDYl9CyDbfij8rqrL5BR+JlIy3jFirEIQq6+iK
YiOioYuEut6SUL6AqBCN9lVKc+tqmcxpq9/D99FkvQKR2QGwptew4ntGcvwYa8NW
cbL3LodQCGtHgCuGsbBWL8KO
-----END RSA PRIVATE KEY-----
)EOF";

#endif // SSL_CERT_H
