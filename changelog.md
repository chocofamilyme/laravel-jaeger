# Changelog

## 2.0.0

- Remove jukylin/jaeger-php dependency (switch to jonahgeorge/jaeger-client-php)
- Add startWithInject method
- Add possibility to configure sampler type and dispatch mode (see config/jaeger.php)
- Switch to Zipkin over compact udp transport by default


Upgrade from v1

- You need to copy config/jaeger.php from v2 to your local project, and change it according your needs