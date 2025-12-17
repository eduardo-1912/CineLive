package pt.ipleiria.estg.dei.amsi.cinelive.activities;

import android.os.Bundle;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;
import androidx.recyclerview.widget.LinearLayoutManager;

import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.adapters.BilhetesAdapter;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivityDetalhesCompraBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.CompraListener;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.DataManager;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Bilhete;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Compra;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ConnectionUtils;

public class DetalhesCompraActivity extends AppCompatActivity {

    ActivityDetalhesCompraBinding binding;
    DataManager manager;
    private BilhetesAdapter adapter;
    int compraId;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);

        binding = ActivityDetalhesCompraBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        setSupportActionBar(binding.toolbar.topAppBar);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setTitle(R.string.title_detalhes_compra);

        // Configurar layout da recycler-view (bilhetes)
        binding.rvBilhetes.setLayoutManager(new LinearLayoutManager(DetalhesCompraActivity.this));

        // Obter o manager
        manager = DataManager.getInstance();

        // Obter ID da compra
        compraId = getIntent().getIntExtra("compraId", -1);

        // Carregar a compra
        loadCompra(true);

        // Swipe refresh
        binding.swipeRefresh.setOnRefreshListener(() -> {
            binding.swipeRefresh.setRefreshing(false);

            // Apenas recarregar sem cache se tiver internet
            if (ConnectionUtils.hasInternet(this)) loadCompra(false);
        });
    }

    private void loadCompra(boolean useCache) {
        binding.mainFlipper.setDisplayedChild(0); // Main Loading
        getSupportActionBar().setTitle(R.string.title_detalhes_compra);

        // Obter compra
        manager.getCompra(this, compraId, useCache, new CompraListener() {
            @Override
            public void onSuccess(Compra compra, List<Bilhete> bilhetes) {
                setCompra(compra, bilhetes);
            }
            @Override
            public void onLocal(Compra compra, List<Bilhete> bilhetes) {
                setCompra(compra, bilhetes);

                // Mudar texto da toolbar
                getSupportActionBar().setTitle(R.string.title_detalhes_compra_local);
            }
            @Override
            public void onError() {
                Toast.makeText(getApplicationContext(), R.string.msg_erro_carregar_compra, Toast.LENGTH_SHORT).show();
                finish();
            }
        });
    }

    private void setCompra(Compra compra, List<Bilhete> bilhetes) {
        binding.mainFlipper.setDisplayedChild(1); // Main Content

        // Preencher dados
        binding.tvTituloFilme.setText(compra.getTituloFilme());
        binding.tvNomeCinema.setText(compra.getNomeCinema());
        binding.tvNomeSala.setText(compra.getNomeSala());
        binding.tvEstado.setText(compra.getEstado());
        binding.tvPagamento.setText(compra.getPagamento());
        binding.tvTotal.setText(compra.getTotal());
        binding.tvDataSessao.setText(compra.getDataSessao());
        binding.tvHoraInicioSessao.setText(compra.getHoraInicioSessao());
        binding.tvHoraFimSessao.setText(compra.getHoraFimSessao());

        // Configurar adapter de bilhetes
        adapter = new BilhetesAdapter(bilhetes);
        binding.rvBilhetes.setAdapter(adapter);
    }

    @Override
    public boolean onSupportNavigateUp() {
        finish();
        return true;
    }
}