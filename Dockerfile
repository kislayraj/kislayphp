FROM php:8.3-cli-bookworm

ARG PIE_VERSION=1.3.8
ARG CORE_VERSION=0.0.3
ARG GATEWAY_VERSION=0.0.2
ARG DISCOVERY_VERSION=0.0.3
ARG QUEUE_VERSION=0.0.1
ARG METRICS_VERSION=0.0.1
ARG CONFIG_VERSION=0.0.1
ARG EVENTBUS_VERSION=0.0.1
ARG PERSISTENCE_VERSION=0.0.1
ARG REQUIRED_EXTENSIONS=""

ENV APP_ENV=production \
    HTTP_PORT=9000 \
    GATEWAY_PORT=9008 \
    DISCOVERY_PORT=9090 \
    DB_PATH=/app/storage/database.sqlite

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        unzip \
        ca-certificates \
        autoconf \
        g++ \
        make \
        pkg-config \
        libcurl4-openssl-dev \
        libzip-dev \
        libssl-dev \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

# Install PIE and KislayPHP extensions from Packagist.
# core is required; other extensions are best-effort by default.
# Set REQUIRED_EXTENSIONS (comma-separated package names) to fail fast in production.
RUN curl -fsSL "https://github.com/php/pie/releases/download/${PIE_VERSION}/pie.phar" -o /usr/local/bin/pie \
    && chmod +x /usr/local/bin/pie \
    && pie --version \
    && pie install --skip-enable-extension "kislayphp/core:${CORE_VERSION}" \
    && EXT_INI="/usr/local/etc/php/conf.d/99-kislayphp.ini" \
    && INSTALLED_PACKAGES="kislayphp/core" \
    && FAILED_PACKAGES="" \
    && echo "extension=kislayphp_extension.so" > "${EXT_INI}" \
    && install_or_warn() { \
        pkg_key="$1"; \
        pkg_spec="$2"; \
        so_name="$3"; \
        if pie install --skip-enable-extension "${pkg_spec}"; then \
            echo "extension=${so_name}" >> "${EXT_INI}"; \
            INSTALLED_PACKAGES="${INSTALLED_PACKAGES} ${pkg_key}"; \
        else \
            echo "WARNING: PIE install failed for ${pkg_spec}. Continuing without ${so_name}." >&2; \
            FAILED_PACKAGES="${FAILED_PACKAGES} ${pkg_key}"; \
        fi; \
    } \
    && install_or_warn "kislayphp/gateway" "kislayphp/gateway:${GATEWAY_VERSION}" "kislayphp_gateway.so" \
    && install_or_warn "kislayphp/discovery" "kislayphp/discovery:${DISCOVERY_VERSION}" "kislayphp_discovery.so" \
    && install_or_warn "kislayphp/queue" "kislayphp/queue:${QUEUE_VERSION}" "kislayphp_queue.so" \
    && install_or_warn "kislayphp/metrics" "kislayphp/metrics:${METRICS_VERSION}" "kislayphp_metrics.so" \
    && install_or_warn "kislayphp/config" "kislayphp/config:${CONFIG_VERSION}" "kislayphp_config.so" \
    && install_or_warn "kislayphp/eventbus" "kislayphp/eventbus:${EVENTBUS_VERSION}" "kislayphp_eventbus.so" \
    && install_or_warn "kislayphp/persistence" "kislayphp/persistence:${PERSISTENCE_VERSION}" "kislayphp_persistence.so" \
    && if [ -n "${REQUIRED_EXTENSIONS}" ]; then \
        for req in $(echo "${REQUIRED_EXTENSIONS}" | tr ',' ' '); do \
            if [ -z "${req}" ]; then \
                continue; \
            fi; \
            case " ${INSTALLED_PACKAGES} " in \
                *" ${req} "*) ;; \
                *) \
                    echo "ERROR: required extension package not installed: ${req}" >&2; \
                    echo "Installed: ${INSTALLED_PACKAGES}" >&2; \
                    echo "Failed: ${FAILED_PACKAGES}" >&2; \
                    exit 1; \
                    ;; \
            esac; \
        done; \
    fi

WORKDIR /app
COPY . /app

RUN mkdir -p /app/storage \
    && touch /app/storage/database.sqlite \
    && chmod -R 775 /app/storage

EXPOSE 9000

HEALTHCHECK --interval=15s --timeout=3s --start-period=10s --retries=5 CMD \
    php -r '$p=(int)(getenv("HTTP_PORT")?:9000); $s=@fsockopen("127.0.0.1",$p,$e,$r,2); if(!$s){exit(1);} fwrite($s,"GET /health HTTP/1.1\r\nHost: localhost\r\nConnection: close\r\n\r\n"); $o=stream_get_contents($s); fclose($s); exit(strpos($o," 200 ")!==false?0:1);'

CMD ["php", "index.php"]
