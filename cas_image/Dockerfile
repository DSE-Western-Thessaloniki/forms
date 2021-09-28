FROM apereo/cas:v6.3.2
EXPOSE 8080 8443
WORKDIR /etc/cas
ADD cas.properties config/
CMD mkdir services
ADD services/* services/
ADD attribute-repository.json .
ENV LANG=en_US.UTF-8 LANGUAGE=en_US:en LC_ALL=en_US.UTF-8
ENV JAVA_VERSION=jdk-11.0.10+9
ENV JAVA_HOME=/opt/java/openjdk
ENV PATH=/opt/java/openjdk/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/opt/java/openjdk/bin:.
WORKDIR /cas-overlay
ENTRYPOINT ["java", "-server", "-noverify", "-Xmx2048M", "-jar", "cas.war"]
