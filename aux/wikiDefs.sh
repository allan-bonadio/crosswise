# meant to be sourced, not run, upon login.

# put something like this in your .profile
#     export wAUX=~/aux
#     export wROOT=~/site
# then this in your bashrc:
#     . $wAUX/wikiDefs.sh
#     alias cwww='cd /dvl/crosswise'


[ -d $wROOT/skins/common ] || echo "\n --- No wROOT set ---\n"

alias ww="cd $wROOT"

#alias wwbin='cd $wROOT/bin'
#alias wwcon='cd $wROOT/config'
alias wwdoc='cd $wROOT/docs'
alias wwext='cd $wROOT/extensions'
alias   wwe='cd $wROOT/extensions'
alias   wwef='cd $wROOT/extensions/featSrc'
#alias wwima='cd $wROOT/images'
alias wwinc='cd $wROOT/includes'
alias   wwi='cd $wROOT/includes'
#alias wwlan='cd $wROOT/languages'
alias wwmai='cd $wROOT/maintenance'
#alias wwmat='cd $wROOT/math'
#alias wwser='cd $wROOT/serialized'

alias wwm='cd $wROOT/maintenance'

alias wwski='cd $wROOT/skins'
alias wws='cd $wROOT/skins'
alias wwsc='cd $wROOT/skins/common'
alias wwsci='cd $wROOT/skins/common/images'

#alias wwtes='cd $wROOT/tests'

# the aux directory
alias wwaux='cd $wAUX'
alias wwa='cd $wAUX'


# move the wROOT dir to here
alias hereww='[ -d skins/common ] && export wROOT=$PWD; cd $wROOT; pwd '
export PATH="$PATH:$wAUX"

