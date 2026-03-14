# Deven

Deven is a tool for managing VMs for development environments. It is a replacement for [Vagrant](https://developer.hashicorp.com/vagrant), though a very minimal one.

Currently only [VirtualBox](https://www.virtualbox.org/) VMs are supported. In practice Deven is mostly a wrapper for calling `VBoxManage`. Only Debian is supported as a guest OS at this time, but others can be added as needed with minimal changes.

## Requirements

* Linux
* PHP
* VirtualBox
* NFS server

## Installation

Clone the repository. Then add `bin/deven` and `bin/deven-ssh` to your *$PATH*.

## Quickstart

* Create `deven.yml` file for your project
* Run `deven image download`
* Run `deven create`
* Run `deven-create-nfs-export` as instructed
* Run `deven create` again
* Run `deven-ssh` to connect via SSH
* Optional: Create `deven-init.sh` file and run `deven init`

See the sections below for more detailed information about each step.

## Config file

Start by adding a config file named `deven.yml` in the root of the project that you want Deven to manage. See `examples/deven.yml`. The only required value in the config file is `name` which must be unique for VMs on your computer. Other configuration values have sensible defaults. Though you will probably want to configure port forwarding at least.

## VM images

First you need to download the VM image that your project uses. Do that by running `deven image download`.

## Creating a VM

Run `deven create` to create the VM. You can optionally add a `--debug` switch to see more information about the raw commands that are being executed.

## Shared directory

The host and guest need to share the project directory for the development environment to work. First I tried to use VirtualBox shared folders but they did not work so good.

Now Deven uses NFS. This means that NFS server is required to be running on the host. Then you must export the project directory by creating a file in `/etc/exports.d/`. Deven provides a helper script for doing that: `bin/deven-create-nfs-export`. This helper script must be run with *sudo*.

The project directory is mounted at `/deven` inside the guest.

## Starting and stopping

Use `deven start` and `deven stop` to start and stop the VM as required.

## Connecting to the VM

Run `deven-ssh` to connect to the VM via SSH. The default SSH port is 2222 on the host.

## Destroying the VM

Run `deven destroy --confirm` to destroy the VM.

## Init script

Deven supports init scripts to set up your development environment inside the VM. Usually this involves installing packages, setting up databases and editing config files. Create a file named `deven-init.sh` in your project. See `examples/deven-init.sh`. This is a standard Linux shell script that is executed with root permissions using `sh` inside the guest.

Run the command `deven init` to execute your init script. A file is created inside the VM to remember that init has been run. Use the `--confirm` switch to run it again if needed.

Now you're ready to start hacking on your project!

## Storage directory

Deven creates a `~/.deven` directory where it stores VM images, SSH keys and temporary files.

## License

MIT
