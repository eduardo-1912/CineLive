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
import pt.ipleiria.estg.dei.amsi.cinelive.models.Bilhete;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Compra;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.NetworkUtils;

public class DetalhesCompraActivity extends AppCompatActivity {

    ActivityDetalhesCompraBinding binding;
    private BilhetesAdapter adapter;

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
        getSupportActionBar().setTitle(R.string.detalhes_compra);

        // Obter ID da compra
        Intent intent = getIntent();
        int idCompra = intent.getIntExtra("compra_id", -1);

        Compra compra = new Compra(1, "Interstellar", "25/11/2025", "CineLive Leiria", "Sala 3", "Confirmada", "10.00€", "25/11/2025", "10:00", "12:00", "A5, A6, A7");

        binding.tvTituloFilme.setText(compra.getTituloFilme());
        binding.tvDataCompra.setText(compra.getDataCompra());
        binding.tvNomeCinema.setText(compra.getNomeCinema());
        binding.tvNomeSala.setText(compra.getNomeSala());
        binding.tvEstado.setText(compra.getEstado());
        binding.tvTotal.setText(compra.getTotal());
        binding.tvDataSessao.setText(compra.getDataSessao());
        binding.tvHoraInicioSessao.setText(compra.getHoraInicioSessao());
        binding.tvHoraFimSessao.setText(compra.getHoraFimSessao());

        List<Bilhete> bilhetes = Arrays.asList(
            new Bilhete(1, "A54FWF", "A5", "8.00€", "Pendente"),
            new Bilhete(2, "B56GFS", "A6", "8.00€", "Confirmado"),
            new Bilhete(3, "ZFDS3D", "A7", "8.00€", "Cancelado"),
            new Bilhete(4, "ZFDS3D", "A7", "8.00€", "Pendente")
        );

        adapter = new BilhetesAdapter(bilhetes);

        binding.rvBilhetes.setLayoutManager(new LinearLayoutManager(this));
        binding.rvBilhetes.setAdapter(adapter);
    }

    @Override
    public boolean onSupportNavigateUp() {
        finish();
        return true;
    }
}