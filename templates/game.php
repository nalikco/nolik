<div class="flex min-h-full items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md space-y-8">
        <h2 class="text-center text-3xl font-bold">Крестики-нолики</h2>
        <h6 class="text-center text-sm text-gray-500"><?=$name?> (<?=$teamStr?>)</h6>
        <div class="grid grid-rows-3 grid-flow-col gap-2">
            <div class="game border py-4 text-center" data-item="0"></div>
            <div class="game border py-4 text-center" data-item="1"></div>
            <div class="game border py-4 text-center" data-item="2"></div>
            <div class="game border py-4 text-center" data-item="3"></div>
            <div class="game border py-4 text-center" data-item="4"></div>
            <div class="game border py-4 text-center" data-item="5"></div>
            <div class="game border py-4 text-center" data-item="6"></div>
            <div class="game border py-4 text-center" data-item="7"></div>
            <div class="game border py-4 text-center" data-item="8"></div>
        </div>
        <div class="gameend text-center"></div>
        <div class="nextbtn" style="display:none;">
            <a href="" class="group relative flex w-full justify-center rounded-md border border-transparent bg-green-600 py-2 px-4 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                Следующая игра
            </a>
        </div>
        <div>
            <a href="/" class="group relative flex w-full justify-center rounded-md border border-transparent bg-red-600 py-2 px-4 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                Выйти
            </a>
        </div>
    </div>
</div>

<script>
    let items = document.getElementsByClassName('game')

    for (let i = 0; i < items.length; i++) {
        items[i].addEventListener("click", () => {
            if(items[i].innerText == '-') update(i + 1)
        })
    }

    update()

    function update(cell = 'none') {
        sendPostRequest('/api/move?cell=' + cell, responseText => {
            let game = JSON.parse(responseText)

            let grid = game.grid

            let items = document.getElementsByClassName('game')

            for (let i = 0; i < items.length; i++) {
                if(grid[i] == 1) {
                    if(game.team == 1) items[i].classList.add('bg-green-200')
                    else items[i].classList.add('bg-red-200')
                    items[i].innerHTML = '0'
                }
                else if(grid[i] == 2) {
                    if(game.team == 2) items[i].classList.add('bg-green-200')
                    else items[i].classList.add('bg-red-200')
                    items[i].innerHTML = 'x'
                }
                else items[i].innerHTML = '-'
            }

            if (game.status === 'win' || game.status === 'draw') {
                let itemMessage = document.getElementsByClassName('gameend')
                let itemNextBtn = document.getElementsByClassName('nextbtn')
                itemNextBtn[0].removeAttribute('style')


                itemMessage[0].innerHTML = game.message
            }
        })
    }

    function sendPostRequest(url, callback) {
        let xmlhttp = new XMLHttpRequest()
        xmlhttp.onreadystatechange = () => {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
                callback(xmlhttp.responseText)
            }
        }
        xmlhttp.open('POST', url, true)
        xmlhttp.send()
    }
</script>