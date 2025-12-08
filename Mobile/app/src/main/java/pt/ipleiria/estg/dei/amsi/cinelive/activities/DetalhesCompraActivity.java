package pt.ipleiria.estg.dei.amsi.cinelive.activities;

import android.content.Intent;
import android.os.Bundle;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;
import androidx.recyclerview.widget.LinearLayoutManager;

import java.util.Arrays;
import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.adapters.BilhetesAdapter;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivityDetalhesCompraBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.CompraListener;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.ComprasManager;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Bilhete;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Compra;

public class DetalhesCompraActivity extends AppCompatActivity {

    ActivityDetalhesCompraBinding binding;
    ComprasManager comprasManager;
    private BilhetesAdapter adapter;
    int id;

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

        comprasManager = ComprasManager.getInstance();

        // Obter ID da compra
        Intent intent = getIntent();
        id = intent.getIntExtra("id", -1);

        loadCompra();
    }

    private void loadCompra() {
        comprasManager.getCompra(this, id, new CompraListener() {
            @Override
            public void onSuccess(Compra compra, List<Bilhete> bilhetes) {

                binding.tvTituloFilme.setText(compra.getTituloFilme());
                binding.tvNomeCinema.setText(compra.getNomeCinema());
                binding.tvNomeSala.setText(compra.getNomeSala());
                binding.tvEstado.setText(compra.getEstado());
                binding.tvTotal.setText(compra.getTotal());
                binding.tvDataSessao.setText(compra.getDataSessao());
                binding.tvHoraInicioSessao.setText(compra.getHoraInicioSessao());
                binding.tvHoraFimSessao.setText(compra.getHoraFimSessao());

                adapter = new BilhetesAdapter(bilhetes);

                binding.rvBilhetes.setLayoutManager(new LinearLayoutManager(DetalhesCompraActivity.this));
                binding.rvBilhetes.setAdapter(adapter);
            }

            @Override
            public void onError() {
                Toast.makeText(DetalhesCompraActivity.this, "erro", Toast.LENGTH_SHORT).show();
            }
        });
    }

    @Override
    public boolean onSupportNavigateUp() {
        finish();
        return true;
    }
}