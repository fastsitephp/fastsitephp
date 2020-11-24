#!/usr/bin/env bash

# =============================================================================
#
#  ------------------------------------------------------------------
#  Encrypt or Decrypt a file and generate Secure Keys for Encryption.
#  ------------------------------------------------------------------
#
#  Author:   Conrad Sollitt
#  Created:  2016 to 2020
#  License:  MIT
#
#  Developer Overview and Notes
#  - This is a Bash Script that runs from shell/terminal on Linux, Unix,
#    and macOS. It also runs on Windows using Windows Subsystem for Linux.
#  - To run, call using Bash:
#      bash encrypt.sh
#    Or set execute permission (if needed) and run directly:
#      chmod +x encrypt.sh
#      ./encrypt.sh
#  - If this script runs without any parameters it will show
#    the help topic with a list of options and examples.
#  - This script includes the ability to run Unit Tests using:
#      bash encrypt.sh -t
#  - This script has no dependencies other than commands that are expected
#    to be installed on most Linux OS's. The actual commands for encryption
#    and decryption work with FreeBSD, however FreeBSD does not include
#    Bash by default. Red Hat, CentoOS, Fedora and some Linux installs won't
#    have the required command [xxd] installed by default so this script
#    provides a warning and info on how to install if needed.
#  - General output messages such as the help topic are 80 characters or less
#    however lines of code may be around 120 characters in length.
#  - This script is linted using [https://www.shellcheck.net/]
#    Currently this script is too large to use the online site so ShellCheck
#    has to be installed to lint. More important than linting are the
#    Unit Tests for this file as it is carefully tested.
#    An option to use ShellCheck now:
#       - Create a Linux Cloud Instance:
#           - sudo apt-get update # Or use the package manager for your OS
#           - sudo apt-get install shellcheck
#           - shellcheck encrypt.sh
#       - Or on Mac (first install homebrew):
#           - brew install shellcheck
#           - Mac uses an older version of shellcheck so run:
#             [shellcheck encrypt.sh --exclude=SC1117]
#       - If it has no output then it ran successfully
#  - This file is included with FastSitePHP [https://www.fastsitephp.com/]
#
# =============================================================================

# Set Bash Options for this Script
set -o pipefail

# Error Codes
# Output for errors is sent to STDERR by using the
# redirection command [>&2] before calling "echo".
ERR_GENERAL=1
ERR_INVALID_OPT=2
ERR_FILE_MISSING=3
ERR_DONT_OVERWITE=4
ERR_SAME_FILE=5
ERR_INVALID_KEY=6
ERR_EMPTY_PASSWORD=7
ERR_LARGE_PASSWORD=8
ERR_FILE_HMAC=9
ERR_UNIT_TEST=10

# Font Formatting for Output
FONT_RESET="\x1B[0m"
FONT_BOLD="\x1B[1m"
FONT_DIM="\x1B[2m"
FONT_UNDERLINE="\x1B[4m"
FONT_WHITE="\x1B[97m"
FONT_BG_RED="\x1B[41m"
FONT_BG_GREEN="\x1B[42m"
FONT_SUCCESS="${FONT_BG_GREEN}${FONT_WHITE}"
FONT_ERROR="${FONT_BG_RED}${FONT_WHITE}"

# Get Path and Name of the Script
SCRIPT_PATH="${BASH_SOURCE[0]}"
SCRIPT_NAME=$(basename "${SCRIPT_PATH}")

# ---------------------------------------------------------
# Main function, this gets called from bottom of the file
# ---------------------------------------------------------
main ()
{
    local msg time_taken
    check_for_xxd
    get_options "$@"
    validate_options
    validate_files
    validate_key
    if [[ "${action}" == "encrypt" ]]; then
        echo "Encrypting file [${in_file}] to [${out_file}] at [$(date)]"
        encrypt
        msg="Success, file [${in_file}] has been encrypted to [${out_file}]."
    else
        echo "Decrypting file [${in_file}] to [${out_file}] at [$(date)]"
        decrypt
        msg="Success, file [${in_file}] has been decrypted to [${out_file}]."
    fi
    time_taken=$(format_time $SECONDS)
    echo "${msg} Time Taken: [${time_taken}]"
}

# -----------------------------------------------------------------------------
# Red Hat, Fedora and some Linux installs won't have [xxd] installed by default.
# This is an initial check to verify it exists. All other commands in this
# script are expected to exist on most Unix and Linux installs.
# -----------------------------------------------------------------------------
check_for_xxd ()
{
    if ! hash xxd 2>/dev/null; then
        >&2 echo -e "${FONT_ERROR}Error${FONT_RESET}, unable to run script because command [xxd] is not installed on this OS"
        >&2 echo "Install using the command below or see documentation for your OS:"
        >&2 echo "    sudo yum install vim-common"
        exit $ERR_GENERAL
    fi
}

# -----------------------------------------------------------------------------
# Help Text, called when passing the
# [-h] option or for invalid or no parameters.
# -----------------------------------------------------------------------------
show_help ()
{
    echo ""
    echo -e "${FONT_BOLD}${FONT_UNDERLINE}Overview:${FONT_RESET}"
    echo "    Encrypt or Decrypt a file using either a Secure Key or a Password."
    echo ""
    echo "    This script uses OpenSSL with strong Security Settings and Algorithms:"
    echo "      - Encryption:    AES-256-CBC"
    echo "      - Verification:  HMAC/SHA-256"
    echo "      - Password:      PBKDF2 using SHA-512 with 200,000 Iterations"
    echo ""
    echo "    A Secure Key can be generated using the [-g] option. If using a"
    echo "    Password instead of a Key expect an extra 1 to 3 seconds of"
    echo "    processing time per file."
    echo ""
    echo "    Unit Tests to verify the system can be run using the [-t] option;"
    echo "    unit tests usually run in 3 to 20 seconds. This script can encrypt"
    echo "    and decrypt files of any size supported by the OS. To verify your"
    echo "    system for large files use the [-l] option; this option will create"
    echo "    files of 1 GB and 3 GB in size and requires at least 9 GB of disk"
    echo "    space. The [-l] option may take anywhere from several minutes to"
    echo "    over 30 minutes depending on your system and disk speed."
    echo ""
    echo -e "${FONT_BOLD}${FONT_UNDERLINE}Usage:${FONT_RESET}"
    script="    bash ${SCRIPT_NAME}"
    infile_param="${FONT_BOLD}-i${FONT_RESET} ${FONT_DIM}<input-file>${FONT_RESET}"
    outfile_param="${FONT_BOLD}-o${FONT_RESET} ${FONT_DIM}<output-file>${FONT_RESET}"
    file_param="${infile_param} ${outfile_param}"
    key_param="${FONT_BOLD}-k${FONT_RESET} ${FONT_DIM}<key>${FONT_RESET}"
    pass_param="${FONT_BOLD}-p${FONT_RESET} ${FONT_DIM}<password>${FONT_RESET}"
    echo -e "${script} ${FONT_BOLD}-g${FONT_RESET}"
    echo -e "${script} ${FONT_BOLD}-e${FONT_RESET} ${file_param} ${key_param}"
    echo -e "${script} ${FONT_BOLD}-e${FONT_RESET} ${file_param} ${pass_param}"
    echo -e "${script} ${FONT_BOLD}-e${FONT_RESET} ${file_param}"
    echo -e "${script} ${FONT_BOLD}-e${FONT_RESET} ${infile_param}"
    echo -e "${script} ${FONT_BOLD}-d${FONT_RESET} ${file_param} ${key_param}"
    echo -e "${script} ${FONT_BOLD}-d${FONT_RESET} ${file_param} ${pass_param}"
    echo -e "${script} ${FONT_BOLD}-d${FONT_RESET} ${file_param}"
    echo -e "${script} ${FONT_BOLD}-d${FONT_RESET} ${infile_param}"
    echo -e "${script} ${FONT_BOLD}-h${FONT_RESET}"
    echo -e "${script} ${FONT_BOLD}-t${FONT_RESET}"
    echo -e "${script} ${FONT_BOLD}-l${FONT_RESET}"
    echo -e "${script} ${FONT_BOLD}-b${FONT_RESET}"
    echo -e "    ${FONT_DIM}# Or run directly in the same directory:${FONT_RESET}"
    echo -e "    ./${SCRIPT_NAME} -g"
    echo -e "    ${FONT_DIM}# If needed set execute permissions:${FONT_RESET}"
    echo -e "    chmod +x ${SCRIPT_NAME}"
    echo -e "    ${FONT_DIM}# To install as a command (if not yet installed):${FONT_RESET}"
    echo -e "    sudo mv ${SCRIPT_NAME} /usr/local/bin/encrypt"
    echo -e "    ${FONT_DIM}# Once installed as a command you can run it from any directory:${FONT_RESET}"
    echo -e "    encrypt"
    echo ""
    echo -e "${FONT_BOLD}${FONT_UNDERLINE}Options:${FONT_RESET}"
    echo "    -h    Help"
    echo "    -g    Generate a new Key, this is random and changes every time"
    echo "    -e    Encrypt a file, requires options [-i], [-o], and ([-k] or [-p])"
    echo "    -d    Decrypt a file, requires options [-i], [-o], and ([-k] or [-p])"
    echo "    -i    Input file, file to encrypt if using [-e] or decrypt if using [-d]"
    echo "    -o    Output file, this file will be created. Optional, defaults to: \".enc\""
    echo "    -k    Key for encryption or decryption"
    echo "    -p    Password for encryption or decryption"
    echo "    -t    Run Core Unit Tests"
    echo "    -l    Run Large File Tests, this may take a while"
    echo "    -b    Run Unit Tests for PBKDF2, this is for Developer Testing"
    echo ""
    echo -e "${FONT_BOLD}${FONT_UNDERLINE}Examples:${FONT_RESET}"
    echo -e "    ${FONT_DIM}# Create a test file [test.txt]${FONT_RESET}"
    echo "    echo \"This is a Test.\" > test.txt"
    echo -e "    ${FONT_DIM}# Generate a Key and save it to file [encryption.key]${FONT_RESET}"
    echo "    bash ${SCRIPT_NAME} -g > encryption.key"
    echo -e "    ${FONT_DIM}# Encrypt the file to [test.enc]${FONT_RESET}"
    # shellcheck disable=SC2016
    echo "    bash ${SCRIPT_NAME} -e -i test.txt -o test.enc -k" '"$(cat encryption.key)"'
    echo -e "    ${FONT_DIM}# Decrypt the file to [test.dec]${FONT_RESET}"
    # shellcheck disable=SC2016
    echo "    bash ${SCRIPT_NAME} -d -i test.enc -o test.dec -k" '"$(cat encryption.key)"'
    echo ""
    echo -e "    ${FONT_DIM}# View the start or end of an of an encrypted file:${FONT_RESET}"
    echo '    head -c 256 test.enc | hexdump -C -v'
    echo '    tail -c 256 test.enc | hexdump -C -v'
    echo ""
    echo -e "    ${FONT_DIM}# When using the default '.enc' file extension${FONT_RESET}"
    echo -e "    ${FONT_DIM}# the name of the output file is not required${FONT_RESET}"
    echo "    bash ${SCRIPT_NAME} -e -i test.txt"
    echo "    bash ${SCRIPT_NAME} -d -i test.txt.enc"
    echo ""
    echo -e "    ${FONT_DIM}# If a file name contains spaces then use quotes.${FONT_RESET}"
    echo -e "    ${FONT_DIM}# This exmple uses a password instead of a key.${FONT_RESET}"
    # shellcheck disable=SC2016
    echo "    bash ${SCRIPT_NAME}" '-e -i "test file.txt" -o "test file.enc" -p "Password123"'
    echo ""
    echo -e "    ${FONT_DIM}# If no key or password is passed to the command${FONT_RESET}"
    echo -e "    ${FONT_DIM}# then you will be prompted for a password.${FONT_RESET}"
    echo "    bash ${SCRIPT_NAME} -e -i test.txt -o test.enc"
    echo ""
}

# -----------------------------------------------------------------------------
# Get Command Line Options
# This function uses [getopts] to read script parameters, this only works here
# because "$@" is passed to the function, otherwise this code would have
# to be at the top script level outside of a function. This method is used
# to keep the code organized into separate functions. [local OPTIND] and the
# ending [shift...] commands are only needed if this function is being called
# twice and this script doesn't call it twice; however, it's good practice to
# have if using [getopts] in a function.
# -----------------------------------------------------------------------------
get_options ()
{
    # If no parameters, display help and exit
    if [[ -z "$1" ]]; then
        show_help
        exit 0
    fi

    # Get options, if the requested command has
    # no parameters then run it and exit
    local OPTIND opt
    while getopts ":gthedlbi:o:k:p:" opt; do
        case "${opt}" in
            e) set_action "encrypt" ;;
            d) set_action "decrypt" ;;
            i) in_file=$OPTARG ;;
            o) out_file=$OPTARG ;;
            k) set_key_or_password "key" "${OPTARG}" ;;
            p) set_key_or_password "password" "${OPTARG}" ;;
            g)
                generate_key
                exit 0
                ;;
            h)
                show_help
                exit 0
                ;;
            t)
                run_unit_tests
                exit 0
                ;;
            l)
                run_large_file_tests
                exit 0
                ;;
            b)
                run_pbkdf2_unit_tests
                exit 0
                ;;
            *)
                >&2 echo ""
                >&2 echo -e "${FONT_ERROR}Error, option is invalid or empty: [-$OPTARG]${FONT_RESET}"
                >&2 echo -e "${FONT_ERROR}To see help with valid options run:${FONT_RESET}"
                >&2 echo -e "${FONT_ERROR}bash ${SCRIPT_NAME} -h${FONT_RESET}"
                >&2 echo ""
                exit $ERR_INVALID_OPT
                ;;
        esac
    done
    shift $((OPTIND-1))
}

# ---------------------------------------------------------
# Called when using options [-e] and [-d]
# ---------------------------------------------------------
set_action ()
{
    # Make sure action is not already set
    if [[ -n "${action}" ]]; then
        >&2 echo ""
        >&2 echo -e "${FONT_ERROR}Error, cannot encrypt and decrypt at the same time.${FONT_RESET}"
        >&2 echo -e "${FONT_ERROR}Specify only [-e] or only [-d] but not both.${FONT_RESET}"
        >&2 echo -e "${FONT_ERROR}To see help with valid options run:${FONT_RESET}"
        >&2 echo -e "${FONT_ERROR}bash ${SCRIPT_NAME} -h${FONT_RESET}"
        >&2 echo ""
        exit $ERR_INVALID_OPT
    fi

    # Set action first time this function is called
    action="$1"
}

# ---------------------------------------------------------
# Called when using options [-k] and [-p]
# ---------------------------------------------------------
set_key_or_password ()
{
    # Make sure key type is not already set
    if [[ -n "${key_type}" ]]; then
        >&2 echo ""
        >&2 echo -e "${FONT_ERROR}Error, cannot use both a key and a password.${FONT_RESET}"
        >&2 echo -e "${FONT_ERROR}Specify only [-k] or only [-p] but not both.${FONT_RESET}"
        >&2 echo -e "${FONT_ERROR}To see help with valid options run:${FONT_RESET}"
        >&2 echo -e "${FONT_ERROR}bash ${SCRIPT_NAME} -h${FONT_RESET}"
        >&2 echo ""
        exit $ERR_INVALID_OPT
    fi

    # Set key type first time this function is called
    key_type="$1"
    if [[ "${key_type}" == "key" ]]; then
        key="$2"
    else
        password="$2"
    fi
}

# ---------------------------------------------------------
# Validate that input parameters are set
# ---------------------------------------------------------
validate_options ()
{
    local error

    # Before validation check if an input file is specified but not an output file.
    # If so then use the default file extension. This requires the file name to end
    # with ".enc" if the file is being decrypted.
    if [[ "${action}" == "encrypt" && -n "${in_file}" && -z "${out_file}" ]]; then
        out_file="${in_file}.enc"
    elif [[ "${action}" == "decrypt" && "${in_file}" == *".enc" && -z "${out_file}" ]]; then
        out_file=${in_file%.enc}
    fi

    # Validation, only a single error message is displayed even if there are multiple errors
    if [[ -z "${in_file}" ]]; then
        error="Error, missing or empty Input File parameter [-i]."
    elif [[ -z "${out_file}" ]]; then
        error="Error, missing or empty Output File parameter [-o]."
    elif [[ -z "${key_type}" ]]; then
        # Default Error Message if no Key or Password
        error="Error, missing or empty parameter. Use [-k] for a key or [-p] for a password."
        # Prompt User for Password as they can manually type a hidden password.
        echo "Encrypt using a Password? If so enter Password or press {return} to exit:"
        read -r -s password
        if [[ ! -z "${password}" ]]; then
            echo -n "Confirm:"
            read -r -s confirm
            echo "" # Extra line break
            if [[ "${password}" != "${confirm}" ]]; then
                error="Error, password and confirmation did not match. Please try again."
            else
                key_type="password"
                return 0
            fi
        fi
    elif [[ -z "${action}" ]]; then
        error="Error, missing parameter. Use [-e] for encryption or [-d] for decryption."
    fi

    if [[ -n "${error}" ]]; then
        >&2 echo ""
        >&2 echo -e "${FONT_ERROR}${error}${FONT_RESET}"
        >&2 echo -e "${FONT_ERROR}To see help with valid options run:${FONT_RESET}"
        >&2 echo -e "${FONT_ERROR}bash ${SCRIPT_NAME} -h${FONT_RESET}"
        >&2 echo ""
        exit $ERR_INVALID_OPT
    fi
}

# ---------------------------------------------------------
# Validate Files and Prompt to Overwrite if needed
# ---------------------------------------------------------
validate_files ()
{
    # Input file needs to exist
    if [[ ! -f "${in_file}" ]]; then
        >&2 echo -e "${FONT_ERROR}Error${FONT_RESET}, input file [${in_file}] doesn't exist"
        exit $ERR_FILE_MISSING
    fi

    # Make sure input and output files are different
    if [[ "${in_file}" == "${out_file}" ]]; then
        >&2 echo -e "${FONT_ERROR}Error${FONT_RESET}, input file and output file are the same"
        exit $ERR_SAME_FILE
    fi

    # Prompt to overwrite in case the output file already exists
    if [[ -f "${out_file}" ]]; then
        echo "The file to create [${out_file}] already exists."
        echo "Do you want to overwite the file? Type [yes] to continue:"
        read -r overwite_file
        if [[ "${overwite_file}" != "yes" ]]; then
            >&2 echo "Ending script, no file was created because it already exists."
            exit $ERR_DONT_OVERWITE
        fi
    fi
}

# ---------------------------------------------------------
# Validate the Input Key and Split into 2 separate keys
# or when using a password make sure it is not empty.
# ---------------------------------------------------------
validate_key ()
{
    # If using a password make sure it is between 1 and 256 characters
    # in length, then exit the function. 256 is the max because of max
    # option limit for [xxd -c] on many systems.
    if [[ "${key_type}" == "password" ]]; then
        if [[ -z "${password}" ]]; then
            >&2 echo -e "${FONT_ERROR}Error, the password cannot be empty.${FONT_RESET}"
            exit $ERR_EMPTY_PASSWORD
        elif (( ${#password} > 256 )); then
            >&2 echo -e "${FONT_ERROR}Error, the password cannot be longer than 256 characters.${FONT_RESET}"
            exit $ERR_LARGE_PASSWORD
        fi
        return 0
    fi

    # Make sure key is a hex string 128 characters in length (64 bytes)
    if [[ ! $key =~ ^[0-9a-f]{128}$ ]]; then
        >&2 echo -e "${FONT_ERROR}Error, invalid key for encryption.${FONT_RESET}"
        >&2 echo -e "${FONT_ERROR}The key must be a hexadecimal string that is 128 characters in length.${FONT_RESET}"
        exit $ERR_INVALID_KEY
    fi

    # Split single key string into 2 separate key strings.
    # One for Encryption and one for HMAC.
    enc_key=${key:0:64}
    hmac_key=${key:64}
}

# ------------------------------------------------------------
# Generate a Key in Hex Format from the System's CSPRNG.
# (Cryptographically secure pseudorandom number generator).
# ------------------------------------------------------------
generate_key ()
{
    # This will output "echo" the key
    xxd -l 64 -c 64 -p /dev/urandom
}

# ---------------------------------------------------------
# Encrypt the File using OpenSSL
# ---------------------------------------------------------
encrypt ()
{
    # Generate the Initialization Vector (IV).
    # The IV is 16 secure random bytes which is the IV size for 'aes-256-cbc'.
    # The value changes every time the function is called.
    iv=$(xxd -l 16 -p /dev/urandom)
    validate_exit_status $? "generating-iv"

    # If using password then convert it to a key.
    # The [iv] will be used as the salt.
    if [[ "${key_type}" == "password" ]]; then
        password_to_key
    fi

    # Encrypt (creates a new file)
    openssl enc -aes-256-cbc -in "${in_file}" -out "${out_file}" -iv "${iv}" -K "${enc_key}"
    validate_exit_status $? "encrypting-file" "${out_file}"

    # Append IV to the end of the file
    echo "${iv}" | xxd -r -p >> "${out_file}"
    validate_exit_status $? "appending-iv" "${out_file}"

    # Calculate and append HMAC
    # shellcheck disable=SC2094,SC2002
    cat "${out_file}" | openssl dgst -sha256 -mac hmac -macopt hexkey:"${hmac_key}" -binary >> "${out_file}"
    validate_exit_status $? "appending-hmac" "${out_file}"
}

# ---------------------------------------------------------
# Decrypt the File using OpenSSL
# ---------------------------------------------------------
decrypt ()
{
    local os tmp_file file_hmac calc_hmac line1 line2 line3

    # Check if Mac or Other OS, this determines how to truncate files.
    os=other
    if [[ "${OSTYPE}" == "darwin"* ]]; then
        os=mac
    fi

    # Copy the original file so that it does not get modified
    tmp_file="${out_file}.tmp"
    cp "${in_file}" "${tmp_file}"
    validate_exit_status $? "copying-file"

    # Get the HMAC from end of the file
    file_hmac=$(tail -c 32 "${tmp_file}" | xxd -l 32 -c 32 -p)
    validate_string_length "${#file_hmac}" 64 "reading-hmac" "${tmp_file}"

    # Truncate the HMAC from end of the file
    #
    # Truncating bytes from the end of a file happens almost instantly with the
    # correct commands while removing bytes from the beginning of a file would
    # require the entire file to be copied which is why the IV and HMAC are appended
    # to the end of the file rather than the beginning of the file. On Linux and most
    # Unix computers the [truncate] command will exist while on macOS it will not
    # exist unless manually installed so a one-line Ruby script is used.
    #
    # The program [stat] will have different options depending on the OS.
    # The "2>/dev/null ||" causes errors to be ignored and the other option to run.
    # In bash "$(( expression ))" is used for math.
    if [[ $os == mac ]]; then
        ruby -e "File.truncate('${tmp_file}', File.size('${tmp_file}')-32)"
        validate_exit_status $? "truncating-hmac-ruby" "${tmp_file}"
    else
        truncate -s $(( $(stat -c%s "${tmp_file}" 2>/dev/null || stat -f%z "${tmp_file}") - 32 )) "${tmp_file}"
        validate_exit_status $? "truncating-hmac" "${tmp_file}"
    fi

    # Get the IV but don't truncate it yet because
    # it must be included for the HMAC calculation.
    iv=$(tail -c 16 "${tmp_file}" | xxd -l 16 -c 16 -p)
    validate_string_length "${#iv}" 32 "reading-iv" "${tmp_file}"

    # If using password then convert it to a key.
    # The [iv] will be used as the salt.
    if [[ "${key_type}" == "password" ]]; then
        password_to_key
    fi

    # Calulate HMAC from the file
    # shellcheck disable=SC2002
    calc_hmac=$(cat "${tmp_file}" | openssl dgst -sha256 -mac hmac -macopt hexkey:"${hmac_key}" -binary | xxd -l 32 -c 32 -p)
    validate_string_length "${#calc_hmac}" 64 "calculating-hmac" "${tmp_file}"

    # Verify that the Saved HMAC and Caculated HMAC are Equal.
    # IMPORTANT - when comparing hashes in an app or website a time-safe
    # compare method would be used in a secure app or site. For example,
    # using [PHP:hash_equals()] or [Python:hmac.compare_digest()]. Since this
    # script is interactive and intended for use by manually typing on a
    # command line a simple if statement is used over time-safe compare.
    if [[ "$file_hmac" != "$calc_hmac" ]]; then
        line1="Error, unable to decrypt file. It was likely encrypted using a different key,"
        line2="from a different program, or has been tampered with. Decryption failed when"
        line3="validating the file so check to make sure your key or password is correct."
        >&2 echo -e "${FONT_ERROR}${line1}${FONT_RESET}"
        >&2 echo -e "${FONT_ERROR}${line2}${FONT_RESET}"
        >&2 echo -e "${FONT_ERROR}${line3}${FONT_RESET}"
        rm "${tmp_file}" # Delete temp file
        exit $ERR_FILE_HMAC
    fi

    # Truncate the IV from end of the file
    if [[ $os == mac ]]; then
        ruby -e "File.truncate('${tmp_file}', File.size('${tmp_file}')-16)"
        validate_exit_status $? "truncating-iv-ruby" "${tmp_file}"
    else
        truncate -s $(( $(stat -c%s "${tmp_file}" 2>/dev/null || stat -f%z "${tmp_file}") - 16 )) "${tmp_file}"
        validate_exit_status $? "truncating-iv" "${tmp_file}"
    fi

    # Decrypt (creates a new file)
    openssl enc -aes-256-cbc -d -in "${tmp_file}" -out "${out_file}" -iv "${iv}" -K "${enc_key}"
    validate_exit_status $? "decrypting-file" "${tmp_file}"

    # Delete the temp file
    rm "${tmp_file}"
    validate_exit_status $? "deleting-temp-file"
}

# -----------------------------------------------------------------------------
# Used during [encrypt] and [decrypt] to validate command exit status's
# -----------------------------------------------------------------------------
validate_exit_status ()
{
    if [[ "$1" != 0 ]]; then
        local lines
        lines=(
            "Unexpected Error, the last command [${2}] failed with error code [${1}]."
            "You may want to check this computer if it has enough disk space."
            "You and use the command [df -h] to view disk stats in 'human readable' format."
        )
        for line in "${lines[@]}"; do
            >&2 echo -e "${FONT_ERROR}${line}${FONT_RESET}"
        done
        if [[ -f "$3" ]]; then
            rm "$3" # Delete Temp File
        fi
        exit $ERR_GENERAL
    fi
}

# -----------------------------------------------------------------------------
# Used during [decrypt] to validate variables are correctly read from the file
# -----------------------------------------------------------------------------
validate_string_length ()
{
    if [[ "$1" != "$2" ]]; then
        local lines
        lines=(
            "Error, unable to decrypt file. It has likely been encrypted from a different"
            "program, is an empty or invalid file, or has been tampered with."
            "The last command to run was [${3}]."
        )
        for line in "${lines[@]}"; do
            >&2 echo -e "${FONT_ERROR}${line}${FONT_RESET}"
        done
        if [[ -f "$4" ]]; then
            rm "$4" # Delete Temp File
        fi
        exit $ERR_GENERAL
    fi
}

# --------------------------------------------------------------------------
# Convert a Password to a Key using using PBKDF2.
# (Password-Based Key Derivation Function 2)
#
# This function generally takes about 0.2 to 3 seconds depending on
# the speed of the computer. The IV is used as the Salt for PBKDF2.
#
# This function calls another language (Node, Python, PHP, or Ruby) based
# on what is installed. While this might sound slow it is relatively quick
# (1/10 of a second or less). Using PBKDF2 is what takes a long time
# however this is by design as it important for security.
#
# This function is not expected to fail because most Linux installs
# will have at least Python 2 or Python 3.
# --------------------------------------------------------------------------
password_to_key ()
{
    # Run functions in order of expected speed (fastest first).
    # Node and Python3 use OpenSSL's C functions if available. PHP uses C but
    # it's not as fast as OpenSSL, however it's faster than Python 2 and Ruby.
    local langs lang
    langs=(node python3 php python ruby)
    for lang in "${langs[@]}"; do
        pbkdf2 "${lang}"
        if check_pbkdf2_result; then
            return
        fi
    done

    # Show an error with a helpful message if no langauge is installed
    >&2 echo ""
    >&2 echo -e "${FONT_ERROR}Error, unable to convert a Password to a Key.${FONT_RESET}"
    >&2 echo "To use a Password for Encryption or Decryption one of the following"
    >&2 echo "languages must be installed on this computer:"
    >&2 echo "    node, python3, python, php, ruby"
    >&2 echo ""
    exit $ERR_GENERAL
}

# -------------------------------------------------------------------
# Call PBKDF2 using the language specified from the first parameter
# -------------------------------------------------------------------
pbkdf2 ()
{
    local lang hex_password param line1 line2 line3 line4 line5 line6 line7 code
    lang="$1"

    # Reset key as this function can be called multiple times
    key=""

    # Make sure the language is installed on the OS
    if ! hash "${lang}" 2>/dev/null; then
        return 1
    fi

    # Convert user text to hex string to prevent escape errors
    hex_password=$(printf "%s" "${password}" | xxd -l 256 -c 256 -p)

    # Get parameter and code based on the language
    case "${lang}" in
        node)
            param="-e"
            line1="const crypto = require('crypto')"
            line2="const password = new Buffer('${hex_password}', 'hex')"
            line3="const salt = new Buffer('${iv}', 'hex')"
            line4="const key = crypto.pbkdf2Sync(password, salt, 200000, 64, 'sha512')"
            line5="console.log(key.toString('hex'))"
            code="${line1}; ${line2}; ${line3}; ${line4}; ${line5};"
            ;;
        python|python3)
            param="-c"
            line1="from __future__ import print_function"
            line2="import hashlib, binascii"
            line3="pw = binascii.unhexlify('${hex_password}')"
            line4="salt = binascii.unhexlify('${iv}')"
            line5="dk = hashlib.pbkdf2_hmac('sha512', pw, salt, 200000, 64)"
            line6="key = binascii.hexlify(dk)"
            line7="print(key.decode())"
            code="${line1}; ${line2}; ${line3}; ${line4}; ${line5}; ${line6}; ${line7}"
            ;;
        php) # Requries PHP 5.5+
            param="-r"
            code="echo hash_pbkdf2('sha512', hex2bin('${hex_password}'), hex2bin('${iv}'), 200000, 128);"
            ;;
        ruby)
            param="-e"
            line1="require 'openssl'"
            line2="digest = OpenSSL::Digest::SHA512.new"
            line3="password = ['${hex_password}'].pack('H*')"
            line4="salt = ['${iv}'].pack('H*')"
            line5="key = OpenSSL::PKCS5.pbkdf2_hmac(password, salt, 200000, 64, digest)"
            line6="puts key.unpack('H*')"
            code="${line1}; ${line2}; ${line3}; ${line4}; ${line5}; ${line6};"
            ;;
        *)
            >&2 echo -e "${FONT_ERROR}UNEXPECTED ERROR - A Code change broke something in this script.${FONT_RESET}"
            exit $ERR_GENERAL;
            ;;
    esac

    # Call the program and evaluate the code
    if key=$("${lang}" "${param}" "${code}" 2>/dev/null); then
        return 0
    fi
    return 1
}

# ---------------------------------------------------------
# Check the result of a PBKDF2 function. This is similar
# to the function [validate_key] however it doesn't exit.
# ---------------------------------------------------------
check_pbkdf2_result ()
{
    # Make sure key is a hex string 128 characters in length (64 bytes)
    if [[ ! $key =~ ^[0-9a-f]{128}$ ]]; then
        return 1
    fi

    # Split single key string into 2 separate key strings.
    # One for Encryption and one for HMAC.
    enc_key=${key:0:64}
    hmac_key=${key:64}
    return 0
}

# -----------------------------------------------------------------------------
# Format time in "mm:ss" or "hh:mm:ss" from the first parameter in seconds
# -----------------------------------------------------------------------------
format_time ()
{
    local h m s
    ((h=$1/3600))
    ((m=$1/60))
    ((s=$1%60))
    if (( h > 0 )); then
        printf "%02d:%02d:%02d" $h $m $s
    else
        printf "%02d:%02d" $m $s
    fi
}

# -------------------------------------------------------------------
# Run all Core Unit Tests, this excludes PBKDF2 Development Tests
# and Large Files Unit Tests. This functions usually runs in
# 3 to 20 seconds depending on the speed of the computer.
# -------------------------------------------------------------------
run_unit_tests ()
{
    unit_test_start "Running Unit Tests"
    unit_test_known_file_key
    unit_test_random_file
    unit_test_run_script_key
    unit_test_decrypt_error_key
    unit_test_script_errors
    # Password Tests run last because they are slower and if not
    # supported by an OS they can be commented out as a group.
    unit_test_known_file_password
    unit_test_run_script_password
    unit_test_decrypt_error_password
    unit_test_end
}

# ---------------------------------------------------------
# Run Large Files Tests. This may take a while to run.
# ---------------------------------------------------------
run_large_file_tests ()
{
    unit_test_start "Running Large File Test"
    unit_test_large_files
    unit_test_end
}

# -------------------------------------------------------------------
# Run PBKDF2 Developer Tests to Verify PBKDF2 with each language
# -------------------------------------------------------------------
run_pbkdf2_unit_tests ()
{
    local title line2 line3
    title="Running Unit Tests for PBKDF2 (Developer Testing)"
    line2="Only 1 of these tests has to succeed in order to use Passwords."
    line3="For all Test to pass, you must have all used languages installed."
    unit_test_start "${title}" "${line2}" "${line3}"

    # Expected Keys
    expected_enc=66a2500ecde20807be6aa90a61d89eac720c0e5925c4c49783eb9031282dbe1f
    expected_hmac=b8fe9340a7b27cc1707d4a813cc96e420448adbfe9ca3d7d5e0857172471d188

    # Setup Password and IV
    unit_test_header "Settings"
    key_type="password"
    unit_test_setup_settings

    # Test each langauge
    unit_test_header "Generating PBKDF2 using Different Languages"
    local langs lang
    langs=(node python3 php python ruby)
    for lang in "${langs[@]}"; do
        unit_test_item "${lang}"
        pbkdf2 "${lang}"
        check_pbkdf2_result
        # shellcheck disable=SC2181
        if [[ $? == 0 && $enc_key == "${expected_enc}" && $hmac_key == "${expected_hmac}" ]]; then
            unit_test_status "Success"
            ((validations++))
        else
            unit_test_status "${FONT_ERROR}Error${FONT_RESET}"
            ((errors++))
        fi
    done

    unit_test_end
}

# -------------------------------------
# Unit Test Start Status
# -------------------------------------
unit_test_start ()
{
    unit_tests=0
    validations=0
    errors=0

    echo ""
    echo "--------------------------------------------------------------------------------"
    echo -e "${FONT_BOLD}${1}${FONT_RESET}"
    if [[ -n "$2" ]]; then
        echo "$2"
    fi
    if [[ -n "$3" ]]; then
        echo "$3"
    fi
    echo "--------------------------------------------------------------------------------"
}

# -------------------------------------
# Output Unit Test Header
# -------------------------------------
unit_test_header ()
{
    echo -e "    ${FONT_BOLD}${FONT_UNDERLINE}Running Test [${1}]${FONT_RESET}"
    ((unit_tests++))
}

# -------------------------------------------------------
# Output Unit Test Item, Status, and Errors
# -------------------------------------------------------
unit_test_item ()   { echo -e "        - ${1}:"; }
unit_test_status () { echo -e "          ${1}"; }
unit_test_error ()  { echo -e "          ${1}" >&2; }

# ----------------------------------------------
# Called at the end of Unit Testing
# ----------------------------------------------
unit_test_end ()
{
    # [$SECONDS] is a built-in variable in Bash
    local time_taken msg
    time_taken=$(format_time $SECONDS)
    time_taken="Time Taken: [${time_taken}]"
    if ((errors == 0)); then
        msg="Success, Completed ${unit_tests} Tests and ${validations} Validations. ${time_taken}"
        msg="${FONT_SUCCESS}${msg}${FONT_RESET}"
    elif ((validations > 0)); then
        msg="Success, Completed ${unit_tests} Tests and ${validations} Validations and ${errors} Errors. ${time_taken}"
        msg="${FONT_SUCCESS}${msg}${FONT_RESET}"
    else
        msg="Error, Completed ${unit_tests} Tests and 0 Validations and ${errors} Errors. ${time_taken}"
        msg="${FONT_ERROR}${msg}${FONT_RESET}"
    fi

    echo "--------------------------------------------------------------------------------"
    echo -e "$msg"
    echo "--------------------------------------------------------------------------------"
    echo ""
    exit 0
}

# ---------------------------------------------------------------
# Create an empty file depending on which command is on the OS.
# This is used with several Unit Tests.
# ---------------------------------------------------------------
create_empty_file ()
{
    if hash mkfile 2>/dev/null; then
        mkfile -n "$2" "$1"
    elif hash xfs_mkfile 2>/dev/null; then
       xfs_mkfile "$2" "$1"
    elif hash fallocate 2>/dev/null; then
        fallocate -l "$2" "$1"
    elif hash truncate 2>/dev/null; then
        truncate -s "$2" "$1"
    elif hash dd 2>/dev/null; then
        # NOTE - [dd] is here mainly as an example. At least one of the above
        # commands is expected to exist on any recent OS. If [truncate] were
        # not included then decryption commands would fail. [dd] should work
        # for the Core Unit Tests but may fail on the large file tests.
        #
        # Warning for the [dd] command: Unix admin joke: "dd stands for disk destroyer"
		# If you are using it manually enter it with caution:
		# https://opensource.com/article/18/7/how-use-dd-linux
        if [[ -n "$3" ]]; then
            dd if=/dev/zero of="$1" bs="$3" count=1 &> /dev/null
        else
            dd if=/dev/zero of="$1" bs="$2" count=1 &> /dev/null
        fi
    else
        >&2 echo "Error, unable to create empty file"
        exit $ERR_UNIT_TEST
    fi
}

# -----------------------------------------------------------------------------
# Specify unit testing secret keys and IV used for encryption and decryption.
# Since this is a published "secret key" DO NOT copy it and use it.
# To create secret keys for using this call [bash encrypt.sh -g].
#
# Each of three random byte paramaters (Enc Key, HMAC Key, and IV)
# are setup below to include a NULL Byte (char 0). Shell Scripts and
# Bash do not allow Null Characters in strings so if the commands
# were setup to use byte string variables then the test would fail.
# Instead they are setup to pipe the output from command to command
# which allows for null characters.
#
# The password includes spaces along with single quote ['] and
# double-quote ["] characters to make sure that escaping works.
# -----------------------------------------------------------------------------
unit_test_setup_settings ()
{
    enc_key=b2e8ff4746c1006adafeb42235554363acf22391941b86b22a7b28c8a591ea4f
    hmac_key=6c3516271b9c008ab4279e5904995aa943117331e3e968560cedb5c7c17266ab
    iv=0ee221ef9e00dfa69efb3b1112bfbb2f
    password="Password \"' 123"

    if [[ "${key_type}" == "key" ]]; then
        unit_test_item "Encryption Key"
        unit_test_status "${enc_key}"
        unit_test_item "HMAC Key"
        unit_test_status "${hmac_key}"
    else
        unit_test_item "Password"
        unit_test_status "${password}"
    fi
    unit_test_item "IV"
    unit_test_status "${iv}"
}

# -----------------------------------------------------------------------------
# Encrypt using a known key for Unit Testing
# The function [encrypt] uses a secure random IV each time and cannot
# use a hard-coded value so this function is only for testing.
# -----------------------------------------------------------------------------
unit_test_create_enc_file ()
{
    unit_test_item "Encrypting File"
    unit_test_status "${out_file}"
    if [[ "${key_type}" == "password" ]]; then
        password_to_key
    fi
    openssl enc -aes-256-cbc -in "${in_file}" -out "${out_file}" -iv ${iv} -K ${enc_key}
    echo ${iv} | xxd -r -p >> "${out_file}"
    # shellcheck disable=SC2094,SC2002
    cat "${out_file}" | openssl dgst -sha256 -mac hmac -macopt hexkey:${hmac_key} -binary >> "${out_file}"
    unit_test_status "Expected Hash: ${1}"
    verify_file_hash_matches "md5" "${out_file}" "$1"
}

# -----------------------------------------------------------------------------
# Verify a file hash matches during Unit Testing
# -----------------------------------------------------------------------------
verify_file_hash_matches ()
{
    # NOTE - this script is using openssl for both MD5 and SHA256. If manually
    # checking an MD5 hash from shell one of the following commands could be
    # used to get only the hash and not the full output:
    #   macOS/FreeBSD:  md5 -q {file}
    #   Linux:          md5sum {file} | cut -d ' ' -f 1
    local hash
    hash=$(openssl dgst -binary -"$1" "$2" | xxd -l 32 -c 32 -p)
    if [[ "${hash}" == "$3" ]]; then
        unit_test_status "File hash using $1 has been verified"
        ((validations++))
    else
        unit_test_error "Hash from file:"
        unit_test_error "${hash}"
        unit_test_error "${FONT_ERROR}Error, hash using $1 does not match the expected value${FONT_RESET}"
        exit $ERR_UNIT_TEST
    fi
}

# -----------------------------------------------------------------------------
# Verify a file hash is different during Unit Testing
# -----------------------------------------------------------------------------
verify_file_hash_is_different ()
{
    local hash
    hash=$(openssl dgst -binary "-${1}" "$2" | xxd -l 32 -c 32 -p)
    if [[ "${hash}" != "$3" ]]; then
        unit_test_status "File hash using ${1} has been verified to be different than the plaintext hash"
        ((validations++))
    else
        unit_test_error "Hash from file:"
        unit_test_error "${hash}"
        unit_test_error "${FONT_ERROR}Error, hash using ${1} matches the plaintext hash${FONT_RESET}"
        exit $ERR_UNIT_TEST
    fi
}

# -----------------------------------------------------------------------------
# Verify a command exit status during Unit Testing
# -----------------------------------------------------------------------------
verify_exit_status ()
{
    if [[ "$1" == "$2" ]]; then
        unit_test_status "Command exit status ${1} has been verified"
        ((validations++))
    else
        unit_test_error "${FONT_ERROR}Error, Command exit status ${1} did not match ${2}${FONT_RESET}"
        exit $ERR_UNIT_TEST
    fi
}

# -----------------------------------------------------------------------------
# Verify that the temp file was deleted on [decrypt] errors
# -----------------------------------------------------------------------------
verify_temp_file_deleted ()
{
    # The temp file from [decrypt()] should be deleted
    if [[ ! -f "${1}.tmp" ]]; then
        unit_test_status "Verified that decrypt temp file has been deleted"
        ((validations++))
    else
        unit_test_error "${FONT_ERROR}Error, temp file for decryption still exists${FONT_RESET}"
        exit $ERR_UNIT_TEST
    fi
}

# ------------------------------------------------------------------------------
# Unit Test to Create, Encrypt, and Decrypt a File using a known Key and IV.
# ------------------------------------------------------------------------------
unit_test_known_file_key ()
{
    local title hash_plain hash_enc
    title="Decryption using a known File, Key, and IV"
    hash_plain=f1c9645dbc14efddc7d8a322685f26eb
    hash_enc=371b4aad41c87bc27bb6cdd58c2c7c48
    key_type="key"
    unit_test_known_file "${title}" $hash_plain $hash_enc
}

# ------------------------------------------------------------------------------
# Unit Test to Encrypt and Decrypt a File using a known Password and IV.
# ------------------------------------------------------------------------------
unit_test_known_file_password ()
{
    local title hash_plain hash_enc
    title="Decryption using a known File, Password, and IV"
    hash_plain=f1c9645dbc14efddc7d8a322685f26eb
    hash_enc=8908ec149e2ae3fa917e75c3f622a29f
    key_type="password"
    unit_test_known_file "${title}" $hash_plain $hash_enc
}

# ------------------------------------------------------------------------------
# Unit Test to Create, Encrypt, and Decrypt a File using a known Key and IV.
# ------------------------------------------------------------------------------
unit_test_known_file ()
{
    unit_test_header "$1"

    # Expected MD5 Hashes
    hash_plain="$2"
    hash_enc="$3"

    # Use Known Keys and IV
    unit_test_setup_settings

    # Create plaintext file of null bytes in temp folder
    unit_test_item "Creating Temp File to Encrypt (10 MB Empty File)"
    tmp_file=$(mktemp)
    in_file="${tmp_file}"
    unit_test_status  "${in_file}"
    create_empty_file "${in_file}" "10m" "10485760"
    unit_test_status "Expected Hash: ${hash_plain}"
    verify_file_hash_matches "md5" "${in_file}" "${hash_plain}"

    # Encrypt
    out_file="${tmp_file}.enc"
    unit_test_create_enc_file "${hash_enc}"

    # Decrypt
    unit_test_item "Decrypting File"
    in_file="${out_file}"
    out_file="${tmp_file}.dec"
    unit_test_status "${out_file}"
    decrypt
    unit_test_status "Expected Hash: ${hash_plain}"
    verify_file_hash_matches "md5" "${out_file}" "${hash_plain}"

    # Delete temp files
    unit_test_item "Deleting temp files"
    rm "${tmp_file}"
    rm "${tmp_file}.enc"
    rm "${tmp_file}.dec"
    unit_test_status "Temp files deleted"
}

# --------------------------------------------------------------------
# Unit Test to Generate a New Key then Encrypt and Decrypt a File.
# --------------------------------------------------------------------
unit_test_random_file ()
{
    unit_test_header "Generate a new Key then Encrypt and Decrypt a File"

    # Generate Key
    unit_test_item "Encrypting with Random Key"
    key=$(generate_key)
    unit_test_status "${key}"

    # Create plaintext file in temp folder
    unit_test_item "Creating Temp File to Encrypt"
    tmp_file=$(mktemp)
    in_file="${tmp_file}"
    unit_test_status "${in_file}"
    content="0123456789 \`~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ"
    echo -n "${content}" >> "${in_file}"
    hash_plain=89872d84b2b966cf1c55882759a4b7773de2be50c7647d59ed1750b15aa8d323
    unit_test_status "Expected Hash: ${hash_plain}"
    verify_file_hash_matches "sha256" "${in_file}" $hash_plain

    # Encrypt
    unit_test_item "Encrypting File"
    out_file="${tmp_file}.enc"
    unit_test_status "${out_file}"
    validate_key
    encrypt

    # Hash must be different than plaintext hash
    verify_file_hash_is_different "sha256" "${out_file}" $hash_plain

    # Decrypt
    unit_test_item "Decrypting File"
    in_file="${out_file}"
    out_file="${tmp_file}.dec"
    unit_test_status "${out_file}"
    decrypt
    unit_test_status "Expected Hash: ${hash_plain}"
    verify_file_hash_matches "sha256" "${out_file}" $hash_plain

    # Delete temp files
    unit_test_item "Deleting temp files"
    rm "${tmp_file}"
    rm "${tmp_file}.enc"
    rm "${tmp_file}.dec"
    unit_test_status "Temp files deleted"
}

# -----------------------------------------------------------------------------
# Unit Test by running this Script with core options using a Key
# -----------------------------------------------------------------------------
unit_test_run_script_key ()
{
    local title
    title="Verify this Script runs with Options using a Key"
    key_type="key"
    unit_test_run_script "${title}"
}

# -----------------------------------------------------------------------------
# Unit Test by running this Script with core options using a Password
# -----------------------------------------------------------------------------
unit_test_run_script_password ()
{
    local title
    title="Verify this Script runs with Options using a Password"
    key_type="password"
    unit_test_run_script "${title}"
}

# -----------------------------------------------------------------------------
# Unit Test by running this Script with core options
# -----------------------------------------------------------------------------
unit_test_run_script ()
{
    unit_test_header "$1"

    # Generate Key or Use a Password
    if [[ "${key_type}" == "key" ]]; then
        unit_test_item "Generating a Random Key with Option [-g]"
        key=$(bash "${SCRIPT_PATH}" -g)
        verify_exit_status $? 0
        unit_test_status "${key}"
    else
        password="Password \"' 123"
        unit_test_item "Using Password"
        unit_test_status "${password}"
    fi

    # Create plaintext file in temp folder
    unit_test_item "Creating Temp File to Encrypt"
    tmp_file=$(mktemp)
    in_file="${tmp_file}"
    unit_test_status "${in_file}"
    content="0123456789 \`~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ"
    echo -n "${content}" >> "${in_file}"
    hash_plain=89872d84b2b966cf1c55882759a4b7773de2be50c7647d59ed1750b15aa8d323
    unit_test_status "Expected Hash: ${hash_plain}"
    verify_file_hash_matches "sha256" "${in_file}" $hash_plain

    # Encrypt
    # The [ &> /dev/null 2>&1 ] is used to suppress all output from a command
    unit_test_item "Encrypting File with Option [-e]"
    out_file="${tmp_file}.enc"
    unit_test_status "${out_file}"
    if [[ "${key_type}" == "key" ]]; then
        # The [-o] options work for both key and password however excluding it on
        # one test allows verification of the default file type '*.enc'
        bash "${SCRIPT_PATH}" -e -i "${in_file}" -k "${key}" &> /dev/null 2>&1
        verify_exit_status $? 0
    else
        bash "${SCRIPT_PATH}" -e -i "${in_file}" -o "${out_file}" -p "${password}" &> /dev/null 2>&1
        verify_exit_status $? 0
    fi

    # Hash must be different than plaintext hash
    verify_file_hash_is_different "sha256" "${out_file}" $hash_plain

    # Decrypt
    unit_test_item "Decrypting File with Option [-d]"
    in_file="${out_file}"
    if [[ "${key_type}" == "key" ]]; then
        unit_test_status "Deleting original temp file as it will be written during decryption"
        rm "${tmp_file}"
        verify_exit_status $? 0
        out_file="${tmp_file}"
    else
        out_file="${tmp_file}.dec"
    fi
    unit_test_status "${out_file}"
    if [[ "${key_type}" == "key" ]]; then
        bash "${SCRIPT_PATH}" -d -i "${in_file}" -k "${key}" &> /dev/null 2>&1
        verify_exit_status $? 0
    else
        bash "${SCRIPT_PATH}" -d -i "${in_file}" -o "${out_file}" -p "${password}" &> /dev/null 2>&1
        verify_exit_status $? 0
    fi
    unit_test_status "Expected Hash: ${hash_plain}"
    verify_file_hash_matches "sha256" "${out_file}" $hash_plain

    # Delete temp files
    unit_test_item "Deleting temp files"
    rm "${tmp_file}"
    rm "${tmp_file}.enc"
    if [[ "${key_type}" == "password" ]]; then
        rm "${tmp_file}.dec"
    fi
    unit_test_status "Temp files deleted"
}

# -----------------------------------------------------------------------------
# Unit Test by running this Script with an Different Key for Decryption
# -----------------------------------------------------------------------------
unit_test_decrypt_error_key ()
{
    local title
    title="Verify that Decryption fails when using a different Key"
    key_type="key"
    unit_test_decrypt_error "${title}"
}

# -----------------------------------------------------------------------------
# Unit Test by running this Script with an Different Password for Decryption
# -----------------------------------------------------------------------------
unit_test_decrypt_error_password ()
{
    local title
    title="Verify that Decryption fails when using a different Password"
    key_type="password"
    unit_test_decrypt_error "${title}"
}

# -----------------------------------------------------------------------------
# Unit Test by running this Script with an Different Key or Password
# -----------------------------------------------------------------------------
unit_test_decrypt_error ()
{
    unit_test_header "$1"

    # Generate Key
    if [[ "${key_type}" == "key" ]]; then
        unit_test_item "Generating a Random Key with Option [-g]"
        key=$(bash "${SCRIPT_PATH}" -g)
        verify_exit_status $? 0
        unit_test_status "${key}"
    else
        password="Password"
        unit_test_item "Using Password"
        unit_test_status "${password}"
    fi

    # Create plaintext file in temp folder
    unit_test_item "Creating Temp File to Encrypt"
    tmp_file=$(mktemp)
    in_file="${tmp_file}"
    unit_test_status "${in_file}"
    content="0123456789 \`~!@#$%^&*()[]{};:<>,.? abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ"
    echo -n "${content}" >> "${in_file}"
    hash_plain=89872d84b2b966cf1c55882759a4b7773de2be50c7647d59ed1750b15aa8d323
    unit_test_status "Expected Hash: ${hash_plain}"
    verify_file_hash_matches "sha256" "${in_file}" $hash_plain

    # Encrypt
    # The [ &> /dev/null 2>&1 ] is used to suppress all output from a command
    unit_test_item "Encrypting File with Option [-e]"
    out_file="${tmp_file}.enc"
    unit_test_status "${out_file}"
    if [[ "${key_type}" == "key" ]]; then
        bash "${SCRIPT_PATH}" -e -i "${in_file}" -o "${out_file}" -k "${key}" &> /dev/null 2>&1
        verify_exit_status $? 0
    else
        bash "${SCRIPT_PATH}" -e -i "${in_file}" -o "${out_file}" -p "${password}" &> /dev/null 2>&1
        verify_exit_status $? 0
    fi

    # Hash must be different than plaintext hash
    verify_file_hash_is_different "sha256" "${out_file}" $hash_plain

    # Generate Key
    if [[ "${key_type}" == "key" ]]; then
        unit_test_item "Generating a new Random Key with Option [-g]"
        key=$(bash "${SCRIPT_PATH}" -g)
        verify_exit_status $? 0
        unit_test_status "${key}"
    else
        password="password"
        unit_test_item "Using Password"
        unit_test_status "${password}"
    fi

    # Decrypt
    unit_test_item "Verifying that file cannot be decrypted with the new key"
    in_file="${out_file}"
    out_file="${tmp_file}.dec"
    unit_test_status "${out_file}"
    if [[ "${key_type}" == "key" ]]; then
        bash "${SCRIPT_PATH}" -d -i "${in_file}" -o "${out_file}" -k "${key}" &> /dev/null 2>&1
        verify_exit_status $? $ERR_FILE_HMAC
    else
        bash "${SCRIPT_PATH}" -d -i "${in_file}" -o "${out_file}" -p "${password}" &> /dev/null 2>&1
        verify_exit_status $? $ERR_FILE_HMAC
    fi

    # No file should have been created
    if [[ ! -f "${out_file}" ]]; then
        unit_test_status "Verified that no file has been created"
        ((validations++))
    else
        unit_test_error "${FONT_ERROR}Error, file for decryption was created${FONT_RESET}"
        exit $ERR_UNIT_TEST
    fi

    # The temp file from [decrypt()] should be deleted
    verify_temp_file_deleted "${out_file}"

    # Delete temp files
    unit_test_item "Deleting temp files"
    rm "${tmp_file}"
    rm "${tmp_file}.enc"
    unit_test_status "Temp files deleted"
}

# -----------------------------------------------------------------------------
# Unit Test by running this Script to generate expected error exit codes.
# -----------------------------------------------------------------------------
unit_test_script_errors ()
{
    # $ERR_UNIT_TEST is not unit tested because it would only occur
    # if a code change breaks something or for an unexpected error.
    # When commands are ran [ &> /dev/null 2>&1 ] is used to redirect
    # STDERR to STDOUT and STDOUT to /dev/null

    unit_test_header "Verify Script Error Exit Codes with Invalid Parameters"

    unit_test_item "Running an unknown Option [-a]"
    bash "${SCRIPT_PATH}" -a &> /dev/null 2>&1
    verify_exit_status $? $ERR_INVALID_OPT

    unit_test_item "Creating Temp File"
    tmp_file=$(mktemp)
    unit_test_status "${tmp_file}"

    unit_test_item "Attempting File encryption and decryption at the same time"
    bash "${SCRIPT_PATH}" -e -d -i "${tmp_file}" -o "${tmp_file}" -k "key" &> /dev/null 2>&1
    verify_exit_status $? $ERR_INVALID_OPT

    unit_test_item "Attempting File encryption to the same file"
    bash "${SCRIPT_PATH}" -e -i "${tmp_file}" -o "${tmp_file}" -k "key" &> /dev/null 2>&1
    verify_exit_status $? $ERR_SAME_FILE

    unit_test_item "Attempting File encryption with an invalid key [key]"
    bash "${SCRIPT_PATH}" -e -i "${tmp_file}" -o "${tmp_file}.enc" -k "key" &> /dev/null 2>&1
    verify_exit_status $? $ERR_INVALID_KEY

    unit_test_item "Attempting File encryption with an empty password"
    bash "${SCRIPT_PATH}" -e -i "${tmp_file}" -o "${tmp_file}.enc" -p "" &> /dev/null 2>&1
    verify_exit_status $? $ERR_EMPTY_PASSWORD

    unit_test_item "Attempting File encryption with a large password - 257 characters"
    password=$(printf ' %.0s' {1..257})
    bash "${SCRIPT_PATH}" -e -i "${tmp_file}" -o "${tmp_file}.enc" -p "${password}" &> /dev/null 2>&1
    verify_exit_status $? $ERR_LARGE_PASSWORD

    unit_test_item "Attempting File encryption with both password and a key"
    bash "${SCRIPT_PATH}" -e -i "${tmp_file}" -o "${tmp_file}" -k "key" -p "password" &> /dev/null 2>&1
    verify_exit_status $? $ERR_INVALID_OPT

    unit_test_item "Attempting File decryption on any empty file"
    bash "${SCRIPT_PATH}" -d -i "${tmp_file}" -o "${tmp_file}.dec" -p "password" &> /dev/null 2>&1
    verify_exit_status $? $ERR_GENERAL
    verify_temp_file_deleted "${tmp_file}.dec"

    unit_test_item "Deleting temp file"
    rm "${tmp_file}"
    unit_test_status "Temp file deleted"

    unit_test_item "Attempting File encryption with a file that doesn't exist"
    bash "${SCRIPT_PATH}" -e -i "${tmp_file}" -o "${tmp_file}.enc" -k "password" &> /dev/null 2>&1
    verify_exit_status $? $ERR_FILE_MISSING
}

# -----------------------------------------------------------------------------
# This test creates, encrypts and decrypts large files (1 GB and 3 GB)
# At least 9 GB of disk space is needed. On some 32-Bit OS's files are
# limited to 2 GB which is why 3 GB is used. First the 1 GB check makes
# sure the function works with large files then if the 3 GB check passes
# files of any size should be able to be encrypted and decrypted with the OS.
# -----------------------------------------------------------------------------
unit_test_large_files ()
{
    # Setup Hard-coded Settings
    echo -e "    ${FONT_BOLD}${FONT_UNDERLINE}Settings${FONT_RESET}"
    key_type="key"
    unit_test_setup_settings

    # Define an Array of Tests, Bash does not support structs, objects, or
    # multi-dimensional arrays so each Test is packed into a string separated
    # by vertical bars (pipe character) '|'.
    tests=(
        "1 GB|1g|cd573cfaace07e7949bc0c46028904ff|6caa8477d12b6cafb47a2ddc2969bcbd"
        "3 GB|3g|c698c87fb53058d493492b61f4c74189|f5aeeb7d2cd73d358783f39a3aaa5821"
    )

    # Loop by array index
    for n in "${!tests[@]}"; do
        # Parse the string of test parameters.
        # By default Bash would parse a string on all spaces so change the
        # IFS (Internal Field Separator) to a vertical bar '|' so that the
        # string is parsed into an array of the correct format.
        IFS='|'
        # shellcheck disable=SC2206
        a=(${tests[n]})
        label=${a[0]}
        size=${a[1]}
        hash_plain=${a[2]}
        hash_enc=${a[3]}
        unset IFS

        # Header
        unit_test_header "Encrypt and Decrypt a ${label} File"

        # Create plaintext file of null bytes in temp folder
        unit_test_item "Creating Temp File to Encrypt (${label} Empty File)"
        tmp_file=$(mktemp)
        in_file="${tmp_file}"
        unit_test_status  "${in_file}"
        create_empty_file "${in_file}" "${size}"
        unit_test_status "Expected Hash: ${hash_plain}"
        verify_file_hash_matches "md5" "${in_file}" "${hash_plain}"

        # Encrypt
        out_file="${tmp_file}.enc"
        unit_test_create_enc_file "${hash_enc}"

        # Delete temp file
        unit_test_item "Deleting plaintext temp file"
        rm "${tmp_file}"
        unit_test_status "File deleted"

        # Decrypt
        unit_test_item "Decrypting File"
        in_file="${out_file}"
        out_file="${tmp_file}.dec"
        unit_test_status "${out_file}"
        decrypt
        unit_test_status "Expected Hash: ${hash_plain}"
        verify_file_hash_matches "md5" "${out_file}" "${hash_plain}"

        # Delete temp files
        unit_test_item "Deleting encryption and decryption temp files"
        rm "${tmp_file}.enc"
        rm "${tmp_file}.dec"
        unit_test_status "Temp files deleted"
    done
}

# --------------------------------------------------------------
# Run the main() function and exit with the result. "$@" is
# used to pass the script parameters to the main function
# and "$?" returns the exit code of the last command to run.
# --------------------------------------------------------------
main "$@"
exit $?
