#!/bin/sh

# TUN device for OpenVPN:
mkdir -p /dev/net
mknod /dev/net/tun c 10 200
chmod 600 /dev/net/tun

# Start OpenVPN Client:
mkdir -p /var/log/openvpn
openvpn --config /etc/openvpn/client/openvpn-client.conf > /var/log/openvpn/openvpn.log 2>&1 &

# Start a persistent process (so that the container will also be persistent):
/usr/bin/tail -f /dev/null
