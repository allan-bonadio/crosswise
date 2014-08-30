# meant to be sourced, not run, upon login.

# put something like this in your .profile
#     export wAUX=~/aux
#     export wROOT=~/site
# then this in your bashrc:
#     . $wAUX/wikiDefs.sh
#     alias cwww='cd /dvl/crosswise'


##### NO!  this sabotages sftp!  [ -d $wROOT/skins/common ] || echo "\n --- No wROOT set ---\n"

# the root
alias wwr='cd $wCW'

# root of the mediawiki source tree
alias ww='cd $wROOT'

# the aux directory
alias wwa='cd $wAUX'

#alias wwbin='cd $wROOT/bin'
#alias wwcon='cd $wROOT/config'
alias wwdoc='cd $wROOT/docs'
alias   wwe='cd $wROOT/extensions'
alias  wwef='cd $wROOT/extensions/featSrc'
#alias wwima='cd $wROOT/images'
alias   wwi='cd $wROOT/includes'
#alias wwlan='cd $wROOT/languages'
#alias wwmat='cd $wROOT/math'
#alias wwser='cd $wROOT/serialized'

alias wwm='cd $wROOT/maintenance'

alias wws='cd $wROOT/skins'
alias wwsc='cd $wROOT/skins/crosswise'

#alias wwtes='cd $wROOT/tests'



## move the wROOT dir to the current directory
#alias hereww='[ -d skins/common ] && export wROOT=$PWD; cd $wROOT; pwd '

export PATH="$PATH:$wAUX"

