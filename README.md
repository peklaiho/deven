# Deven

Deven is a tool for managing VMs for development environments.

The purpose is to be a very minimal replacement for [Vagrant](https://developer.hashicorp.com/vagrant). Currently only [VirtualBox](https://www.virtualbox.org/) VMs are supported.

## Usage

Clone the repo, run `composer install` and add `bin/deven` to your $PATH.

Place a `deven.yml` file in the directory of your project that you want Deven to manage. See `deven-example.yml` for an example.

## License

MIT
