FROM node:20-alpine

RUN npm install -g @angular/cli http-server

WORKDIR /app

CMD ["sh", "-c", "if [ -f package.json ]; then npm install && ng serve --host 0.0.0.0 --port 4200; else http-server /app -p 4200 -a 0.0.0.0; fi"]
