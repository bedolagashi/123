
export PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin:/root/bin

LOGFOLDER=/tmp

LOGFILE=${LOGFOLDER}/fakepop.log

LOGGING=1
DEBUG=1
TIMEOUT=60

POP_USER=
POP_PASSWRD=test

LINES=1
BREAK=0

write_log () {
  if [ ${LINES} -eq 1 ] ; then
    echo '---'  >>${LOGFILE}
  fi
  let LINES+=1
  [ ${LOGGING} = 0 ] || echo -e "`date '+%b %d %H:%M'` pop3 $*" >>${LOGFILE}
}

ANSWER="+OK Fake POP3 Service Ready"

while [ ${BREAK} -eq 0 ] ; do
  echo -en "${ANSWER}\r\n"

  REPLY=""

  
  read -t ${TIMEOUT}

  ANSWER="+OK "
  COMMAND=""
  ARGS=""
  TIMEOUT=30

  if [ "$REPLY" ] ; then
    write_log "RAW input: '`echo "${REPLY}" | tr -cd '[ -~]'`'"

    COMMAND="`echo "${REPLY}" | awk '{print $1}' | tr -cd '\40-\176' | tr 'a-z' 'A-Z'`"
    ARGS="`echo "${REPLY}"    | tr -cd '\40-\176' | awk '{for(i=2;i<=NF;i++){printf "%s ", $i};printf "\n"}' | sed 's/ $//'`"

    write_log "Command: \"${COMMAND}\""
    write_log "Arguments: \"${ARGS}\""

    case "$COMMAND" in
      QUIT)
        break
        ;;
      USER)
        if [ -n "${ARGS}" ] ; then
          POP_USER="${ARGS}"
          ANSWER="+OK Please send PASS command"
        fi
        ;;
      AUTH)
        ANSWER="+OK \r\n."
        ;;
      CAPA)
        ANSWER="+OK Capabilities include\r\nUSER\r\nCAPA\r\n."
        ;;
      PASS)
        if [ "${POP_PASSWRD}" == "${ARGS}" ] ; then
          ANSWER="+OK Logged in."
          AUTH=1
        else
          ANSWER="-ERR Login failed."
        fi
        ;;
      LIST)
        if [ "${AUTH}" = 0 ] ; then
          ANSWER="-ERR Not authenticated"
        else
          if [ -z "${ARGS}" ] ; then
            ANSWER="+OK No messages, really\r\n."
          else
            ANSWER="-ERR No messages, no list, no status"
          fi
        fi
        ;;
      RSET)
        ANSWER="+OK Resetting or whatever\r\n."
        ;;
      LAST)
        if [ "${AUTH}" = 0 ] ; then
          ANSWER="-ERR Not authenticated"
        else
          ANSWER="+OK 0"
        fi
        ;;
      STAT)
        if [ "${AUTH}" = 0 ] ; then
          ANSWER="-ERR Not authenticated"
        else
          ANSWER="+OK 0 0"
        fi
        ;;
      NOOP)
        ANSWER="+OK Hang on, doing nothing"
        ;;
     esac
  else
    echo "+OK Connection timed out"
    break
  fi
done

echo "+OK Bye!\r\n"
