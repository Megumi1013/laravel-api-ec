Vagrant.configure("2") do |config|

  config.vm.box = "ubuntu/bionic64"
  config.vm.provision "shell", :path => "shell/provision.sh"
  config.vm.synced_folder ".", "/webdev/", id: "vagrant-root", mount_options: ["rw", "tcp", "nolock", "noacl", "async"], type: "nfs", nfs_udp: false

  config.vm.network "forwarded_port", host: 1487, guest: 80
  config.vm.network "forwarded_port", host: 1488, guest: 443
  config.vm.network "private_network", ip: "192.168.118.118"

  config.hostmanager.enabled = true
  config.hostmanager.manage_host = true
  config.hostmanager.manage_guest = true
  config.hostmanager.ignore_private_ip = false
  config.hostmanager.include_offline = true

  config.vm.hostname = 'laravel-api-ec'
  config.hostmanager.aliases = %w(laravel-api-ec.megumidev)

  config.vm.provider "virtualbox" do |v|

    v.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
    v.customize ["modifyvm", :id, "--ioapic", "on"]

    v.cpus = 2

    host = RbConfig::CONFIG['host_os']

    # Give VM 1/4 system memory
    if host =~ /darwin/
      mem = `sysctl -n hw.memsize`.to_i / 1024
    elsif host =~ /linux/
      mem = `grep 'MemTotal' /proc/meminfo | sed -e 's/MemTotal://' -e 's/ kB//'`.to_i
    elsif host =~ /mswin|mingw|cygwin/
      mem = `wmic computersystem Get TotalPhysicalMemory`.split[1].to_i / 1024
    end

    mem = mem / 1024 / 4

    v.memory = mem

  end

  # Disable guest additions auto update
  if Vagrant.has_plugin?("vagrant-vbguest")
      config.vbguest.auto_update = false
  end

end
