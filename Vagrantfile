require 'yaml'

dir         = File.dirname(File.expand_path(__FILE__))
configData  = YAML.load_file("#{dir}/kuheylan/config.yaml")
data        = configData['kuheylan-default']

Vagrant.require_version '>= 1.6.0'

Vagrant.configure(2) do |config|

  config.ssh.insert_key = false

  config.vm.box     = "#{data['vm']['box']}"
  config.vm.box_url = "#{data['vm']['box_url']}"

  if data['vm']['hostname'].to_s.strip.length != 0
    config.vm.hostname = "#{data['vm']['hostname']}"
  end

  if data['vm']['network']['private_network'].to_s != ''
    config.vm.network 'private_network', ip: "#{data['vm']['network']['private_network']}"
  end

  data['vm']['network']['forwarded_port'].each do |i, port|
    if port['guest'] != '' && port['host'] != ''
      config.vm.network :forwarded_port, guest: port['guest'].to_i, host: port['host'].to_i
    end
  end


  data['vm']['synced_folder'].each do |i, folder|
    if folder['source'] != '' && folder['target'] != ''
      sync_owner = !folder['sync_owner'].nil? ? folder['sync_owner'] : 'www-data'
      sync_group = !folder['sync_group'].nil? ? folder['sync_group'] : 'www-data'

      if folder['sync_type'] == 'nfs'
        config.vm.synced_folder "#{folder['source']}", "#{folder['target']}", id: "#{i}", type: 'nfs'
        if Vagrant.has_plugin?('vagrant-bindfs')
          config.bindfs.bind_folder "#{folder['target']}", "/mnt/vagrant-#{i}"
        end
      elsif folder['sync_type'] == 'smb'
        config.vm.synced_folder "#{folder['source']}", "#{folder['target']}", id: "#{i}", type: 'smb'
      elsif folder['sync_type'] == 'rsync'
        rsync_args = !folder['rsync']['args'].nil? ? folder['rsync']['args'] : ['--verbose', '--archive', '-z']
        rsync_auto = !folder['rsync']['auto'].nil? ? folder['rsync']['auto'] : true
        rsync_exclude = !folder['rsync']['exclude'].nil? ? folder['rsync']['exclude'] : ['.vagrant/']

        config.vm.synced_folder "#{folder['source']}", "#{folder['target']}", id: "#{i}",
          rsync__args: rsync_args, rsync__exclude: rsync_exclude, rsync__auto: rsync_auto, type: 'rsync', group: sync_group, owner: sync_owner
      elsif data['vm']['chosen_provider'] == 'parallels'
        config.vm.synced_folder "#{folder['source']}", "#{folder['target']}", id: "#{i}",
          group: sync_group, owner: sync_owner, mount_options: ['share']
      else
        config.vm.synced_folder "#{folder['source']}", "#{folder['target']}", id: "#{i}",
          group: sync_group, owner: sync_owner, mount_options: ['dmode=775', 'fmode=764']
      end
    end
  end

  config.vm.provision :shell, :path => 'kuheylan/scripts/provision.sh'
  config.vm.provision :shell, run: 'always' do |s|
    s.path = 'kuheylan/scripts/always.sh'
    s.args = ['startup-once', 'startup-always']
  end



end
