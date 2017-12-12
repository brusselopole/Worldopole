docker build -t worldopole .
docker run -dt --name worldopole -v /etc/letsencrypt/:/etc/letsencrypt/ -p 8989:80 -p 9898:443 worldopole

#TODO
fix timezone
generate certs
link to db
documentation