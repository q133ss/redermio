FROM node:20-alpine

RUN npm install -g @angular/cli

WORKDIR /app

CMD ["sh", "-lc", "if [ ! -d node_modules ] || [ ! -f node_modules/@angular/cli/package.json ]; then npm install; fi && ng serve --host 0.0.0.0 --port 4200 --poll 2000"]
