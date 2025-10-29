import { init, retrieveRawInitData } from '@telegram-apps/sdk';
import { isTMA } from '@telegram-apps/bridge';
import axios from 'axios';

if (isTMA()) {
    init();

    const url = document.querySelector('meta[name="url"]').getAttribute('content');

    axios.post(url, {}, {
        headers: {
            Authorization: `tma ${retrieveRawInitData()}`
        },
    })
        .then(response => response.data)
        .then(data => {
            window.location.href = data.url;
        })
        .catch(error => {
            alert(error.data.message);
        });
} else {
    alert('The request is not from the Telegram mini app.');
}

