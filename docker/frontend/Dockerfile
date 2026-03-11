FROM node:20-alpine

RUN npm install -g @angular/cli

COPY docker/frontend/entrypoint.sh /usr/local/bin/frontend-entrypoint.sh
RUN chmod +x /usr/local/bin/frontend-entrypoint.sh

WORKDIR /app

ENTRYPOINT ["/usr/local/bin/frontend-entrypoint.sh"]
