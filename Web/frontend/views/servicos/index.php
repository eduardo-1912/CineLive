<div class="container my-5">
    <div class="row align-items-start">
        <!-- Formulário -->
        <div class="col-md-8">
            <h4 class="fw-bold">Formulário de Contacto</h4>
            <p class="text-muted mb-4">Envia-nos uma mensagem ou um pedido de aluguer de sala privada.</p>

            <form>
                <div class="row mb-3">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" placeholder="John Smith">
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" placeholder="john.smith@email.com">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="mensagem" class="form-label">Informações Adicionais</label>
                    <textarea class="form-control" id="mensagem" rows="5"></textarea>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-dark w-100">Submeter</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Cartão lateral -->
        <div class="col-md-4 d-flex flex-column align-items-stretch">
            <div class="bg-light rounded-4 text-center py-5 shadow-sm mb-3">
                <div class="mb-3">
                    <i class="bi bi-calendar3" style="font-size: 3rem;"></i>
                </div>
                <h5 class="fw-bold mb-1">Aluga uma sala</h5>
                <p class="text-muted mb-0">Celebra com familiares e amigos.</p>
            </div>
            <button type="button" class="btn btn-dark w-100">Iniciar Sessão</button>
        </div>
    </div>
</div>
