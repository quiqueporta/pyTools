_pytools()
{
    local cur opts
    COMPREPLY=()
    cur="${COMP_WORDS[COMP_CWORD]}"
    opts=$($(which pytools) list --raw | awk '{ print $1 }')
    COMPREPLY=( $(compgen -W "${opts}" -- ${cur}) )
    return 0
}
complete -o default -F _pytools pytools
COMP_WORDBREAKS=${COMP_WORDBREAKS//:}
