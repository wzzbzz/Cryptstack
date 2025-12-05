import { cribData } from './cribs.js';
import { prompts } from './prompts.js';

// ---- INITIAL STACK ----
let currentStack = ["GiveItToMeStraight"];

// ---- BUILD CRIB TABLE ----
function loadCribTable() {
    const tbody = document.getElementById('crib-body');
    tbody.innerHTML = "";

    const cribSet = document.getElementById("cribset-select").value;
    cribData[cribSet].forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${row.plain}</td>
            <td class="cipher">${row.cipher}</td>
            <td class="decoded">${row.decoded || ""}</td>
        `;
        tbody.appendChild(tr);
    });
}

function renderStack() {
    const ul = document.getElementById("stack-list");
    ul.innerHTML = "";

    currentStack.forEach((method, index) => {
        const li = document.createElement("li");
        li.dataset.index = index;
        li.textContent = method;

        const remove = document.createElement("span");
        remove.className = "remove";
        remove.textContent = "✕";
        li.appendChild(remove);

        remove.addEventListener("click", () => {
            currentStack.splice(index, 1);
            renderStack();
        });

        // ---- DESKTOP DRAG ----
        li.draggable = true;

        li.addEventListener("dragstart", (e) => {
            e.dataTransfer.setData("index", index);
            li.classList.add("dragging");
        });

        li.addEventListener("dragend", () => {
            li.classList.remove("dragging");
        });

        li.addEventListener("dragover", (e) => {
            e.preventDefault();
        });

        li.addEventListener("drop", (e) => {
            e.preventDefault();
            const from = parseInt(e.dataTransfer.getData("index"), 10);
            const to = index;
            moveItem(from, to);
        });

        // ---- MOBILE TOUCH DRAG ----
        let startY = 0;
        let startIndex = index;

        li.addEventListener("touchstart", (e) => {
            startY = e.touches[0].clientY;
            startIndex = index;
            li.classList.add("dragging");
        });

        li.addEventListener("touchmove", (e) => {
            e.preventDefault();

            const touchY = e.touches[0].clientY;
            const delta = touchY - startY;

            const items = [...ul.children];
            const newIndex = Math.min(
                items.length - 1,
                Math.max(0, startIndex + Math.round(delta / 40)) // 40px per item
            );

            if (newIndex !== startIndex) {
                moveItem(startIndex, newIndex);
                startIndex = newIndex;
            }
        });

        li.addEventListener("touchend", () => {
            li.classList.remove("dragging");
        });

        ul.appendChild(li);
    });

    function moveItem(from, to) {
        const item = currentStack.splice(from, 1)[0];
        currentStack.splice(to, 0, item);
        renderStack();
    }
}


// ---- ADD NEW LAYER ----
document.getElementById("add-layer").addEventListener("change", e => {
    const layer = e.target.value;
    if (!layer) return;

    currentStack.push(layer);
    renderStack();
    e.target.value = "";
});


async function generateCrib() {

    // diable crib buttons
    document.getElementById("copy-json").disabled = true;
    document.getElementById("copy-full-prompt").disabled = true;
    // disable the show crib button
    const toggleButton = document.getElementById("toggle-crib");
    toggleButton.disabled = true;
    toggleButton.textContent = "Generating Crib...";
    toggleButton.style.color = "#888";
    // clear the crib table
    loadCribTable();

    const cribSet = document.getElementById("cribset-select").value;
    const items = cribData[cribSet].map(x => x.plain);


    const results = [];
    console.log("start");
    console.log(items.length + " items");
    for (const item of items) {
        // stack send comma separated string
        const stackString = currentStack.join(",");
        const response = await fetch("/encode", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                stack: stackString,
                text: item,
                key: document.getElementById("key-input").value
            })
        });

        const json = await response.json();

        //now decode using encoded, to verify that it's working
        const decodeResponse = await fetch("/decode", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                stack: stackString,
                text: json.encoded,
                key: document.getElementById("key-input").value
            })
        });
        const decodeJson = await decodeResponse.json();
        results.push({ output: json.encoded, decoded: decodeJson.decoded });
    }
    console.log("done");

    updateCribTable(results);
    updateCribData(results);

    // enable crib buttons
    document.getElementById("copy-json").disabled = false;
    document.getElementById("copy-full-prompt").disabled = false;
    // enable the show crib button
    document.getElementById("toggle-crib").disabled = false;

    const cribDiv = document.getElementById('crib');
    toggleButton.textContent = cribDiv.classList.contains('closed')
        ? 'Show Crib Sheet'
        : 'Hide Crib Sheet';
    toggleButton.style.color = "";

}


// ---- UPDATE CRIB TABLE WITH RESULTS ----
function updateCribTable(results) {
    const rows = document.querySelectorAll("#crib-body tr");

    results.forEach((res, i) => {

        console.log(res);
        const row = rows[i];

        const outputCell = row.querySelector(".cipher");

        outputCell.textContent = res.output;

        const cribSet = document.getElementById("cribset-select").value;
        cribData[cribSet][i].cipher = res.output;
        cribData[cribSet][i].decoded = res.decoded;

        // put a green check in the "decoded" column if it matches the plain text
        const decodedCell = row.querySelector(".decoded");
        if (res.decoded === cribData[cribSet][i].plain) {
            decodedCell.innerHTML = `<span class="status-ok">✔</span>`;
        } else {
            decodedCell.innerHTML = `<span class="status-fail">✘</span>`;
        }

    });
}

// ---- UPDATE CRIB DATA WITH RESULTS ----
function updateCribData(results) {
    const cribSet = document.getElementById("cribset-select").value;
    results.forEach((res, i) => {
        cribData[cribSet][i].cipher = res.output;
    });
}



// ---- ENCODE SINGLE INPUT ----
document.getElementById("encode-submit").addEventListener("click", async () => {
    const inputText = document.getElementById("encode-input").value;
    if (!inputText) return;
    const stackString = currentStack.join(",");
    const response = await fetch("/encode", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            stack: stackString,
            text: inputText,
            key: document.getElementById("key-input").value
        })
    });
    const json = await response.json();
    document.getElementById("encode-output").textContent = json.encoded;
});
// ---- COPY TO CLIPBOARD ----
document.getElementById("copy-encoded").addEventListener("click", () => {
    const outputText = document.getElementById("encode-output").textContent;
    if (!outputText) return;
    navigator.clipboard.writeText(outputText).then(() => {
        alert("Encoded text copied to clipboard!");
    });
});

// ---- DECODE SINGLE INPUT ----
document.getElementById("decode-submit").addEventListener("click", async () => {
    const inputText = document.getElementById("decode-input").value;
    if (!inputText) return;
    const stackString = currentStack.join(",");
    const response = await fetch("/decode", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            stack: stackString,
            text: inputText,
            key: document.getElementById("key-input").value
        })
    });
    const json = await response.json();
    document.getElementById("decode-output").textContent = json.decoded;
});
// ---- COPY TO CLIPBOARD ----
document.getElementById("copy-decoded").addEventListener("click", () => {
    const outputText = document.getElementById("decode-output").textContent;
    if (!outputText) return;
    navigator.clipboard.writeText(outputText).then(() => {
        alert("Decoded text copied to clipboard!");
    });
});


// ---- COPY CRIB JSON TO CLIPBOARD ----
document.getElementById("copy-json").addEventListener("click", () => {
    const cribJson = JSON.stringify(cribData, null, 2);
    navigator.clipboard.writeText(cribJson).then(() => {
        alert("Crib JSON copied to clipboard!");
    });
});

// ---- CLEAR STACK ----
document.getElementById("clear-stack").addEventListener("click", () => {
    currentStack = [];
    renderStack();
});


async function previewEncodeDecode() {
    const plaintext = document.getElementById("plaintext").textContent;
    const stackString = currentStack.join(",");
    const key = document.getElementById("key-input").value;

    // Encode
    const encodeRes = await fetch("/encode", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            stack: stackString,
            text: plaintext,
            key: key
        })
    });
    const encodeJson = await encodeRes.json();
    document.getElementById("ciphertext").textContent = encodeJson.encoded;

    // Decode
    const decodeRes = await fetch("/decode", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            stack: stackString,
            text: encodeJson.encoded,
            key: key
        })
    });
    const decodeJson = await decodeRes.json();

    // Validate
    const validDiv = document.getElementById("decryption_valid");
    if (decodeJson.decoded === plaintext) {
        validDiv.innerHTML = `<span class="status-ok">✔</span>`;
    } else {
        validDiv.innerHTML = `<span class="status-fail">✘</span>`;
    }
}

window.addEventListener("DOMContentLoaded", switchon);

function switchon() {
    wires()
    screens();
}

function wires() {
    // Optionally, call previewEncodeDecode() whenever stack/key/plaintext changes:
    document.getElementById("key-input").addEventListener("input", screens);
    document.getElementById("plaintext").addEventListener("input", screens);
    document.getElementById("cribset-select").addEventListener("change", generateCrib);
    document.getElementById("stack-list").addEventListener("DOMSubtreeModified", screens);

  
    const toggleBtn = document.getElementById('toggle-crib');
    const cribDiv = document.getElementById('crib');

    const updateBtnText = () => {
        toggleBtn.textContent = cribDiv.classList.contains('closed')
            ? 'Show Crib Sheet'
            : 'Hide Crib Sheet';
    };

    // Smart initial state: open on desktop/tablet, closed on phone
    if (window.innerWidth > 768) {
        cribDiv.classList.remove('closed');
    } else {
        cribDiv.classList.add('closed');
    }

    updateBtnText();

    toggleBtn.addEventListener('click', () => {
        cribDiv.classList.toggle('closed');
        updateBtnText();
    });


    // ---- generate crib ----

    // Run previewEncodeDecode whenever stack changes
    const stackObserver = new MutationObserver(() => {
        screens();
    });
    stackObserver.observe(stackList, { childList: true, subtree: false });

    document.getElementById("copy-full-prompt").addEventListener("click", () => {
        const preface = document.getElementById("experiment-prompt").value;
        const plaintext = document.getElementById("experiment-plaintext").value;

        let prompt = preface + "\n\nPlaintext: " + plaintext;

        navigator.clipboard.writeText(prompt).then(() => {
            //alert("Full prompt copied to clipboard!");
            // look for %ciphertext% and %crib% and replace with actual values
            const cribSet = document.getElementById("cribset-select").value;
            const cribLines = cribData[cribSet].map(row => `Plain: ${row.plain}  Cipher: ${row.cipher}`);
            const cribText = cribLines.join("\n");
            let finalPrompt = preface.replace("%ciphertext%", document.getElementById("experiment-ciphertext").textContent)
                .replace("%crib%", cribText);
            navigator.clipboard.writeText(finalPrompt).then(() => {
                alert("Full prompt with crib copied to clipboard!");
            });
        });
    });



}

function screens() {
    previewEncodeDecode();
    generateCrib();
    initializeExperimentSection();
}


function initializeExperimentSection() {
    const plaintextInput = document.getElementById("experiment-plaintext");
    const ciphertextDiv = document.getElementById("experiment-ciphertext");

    getRandomPlaintext();

    function getRandomPlaintext() {
        const randomIndex = Math.floor(Math.random() * plaintexts.length);
        plaintextInput.value = plaintexts[randomIndex];
    }

    getRandomPrompt();

    function getRandomPrompt() {
        const randomIndex = Math.floor(Math.random() * prompts.length);
        document.getElementById("experiment-prompt").value = prompts[randomIndex];
    }

    async function updateCiphertext() {
        const plaintext = plaintextInput.value;
        if (!plaintext) {
            ciphertextDiv.textContent = "";
            return;
        }
        const stackString = currentStack.join(",");
        const response = await fetch("/encode", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                stack: stackString,
                text: plaintext,
                key: document.getElementById("key-input").value
            })
        });
        const json = await response.json();
        ciphertextDiv.textContent = json.encoded;
    }

    plaintextInput.addEventListener("input", updateCiphertext);
    // Initial call
    updateCiphertext();
}
const stackList = document.getElementById("stack-list");


// Also run after renderStack (for programmatic changes)
const originalRenderStack = renderStack;
renderStack = function () {
    originalRenderStack.apply(this, arguments);
    previewEncodeDecode();
};


// ---- INITIALIZE ----
loadCribTable();
renderStack();


export const plaintexts = [
    "Let it be, let it be, let it be, let it be",
    "Yesterday, all my troubles seemed so far away",
    "Hey Jude, don't make it bad",
    "Here comes the sun, and I say, it's all right",
    "Imagine all the people living life in peace",
    "I can't get no satisfaction",
    "Cause I'm as free as a bird now",
    "Is this the real life? Is this just fantasy?",
    "I'm going to make him an offer he can't refuse.",
    "May the Force be with you.",
    "Here's looking at you, kid.",
    "You talking to me?",
    "I love the smell of napalm in the morning.",
    "Frankly, my dear, I don't give a damn.",
    "Go ahead, make my day.",
    "Say hello to my little friend!",
    "Houston, we have a problem.",
    "Nobody puts Baby in a corner."
];
