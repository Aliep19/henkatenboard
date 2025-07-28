<button onclick="cek()">Cek Session</button>

<script>
function cek() {
    fetch('../proses/session/check_session.php')
        .then(res => res.json())
        .then(data => alert("Status: " + data.status));
}
</script>