package pt.ipleiria.estg.dei.amsi.cinelive.activities;

import static androidx.core.content.ContentProviderCompat.requireContext;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import com.bumptech.glide.Glide;
import com.bumptech.glide.load.engine.DiskCacheStrategy;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivityDetalhesFilmeBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Filme;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Sessao;

public class DetalhesFilmeActivity extends AppCompatActivity {

    ActivityDetalhesFilmeBinding binding;
    Sessao sessaoSelecionada;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);

        binding = ActivityDetalhesFilmeBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        setSupportActionBar(binding.toolbar.topAppBar);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);

        // Obter ID do Filme
        Intent intentFilmes = getIntent();
        int idFilme = intentFilmes.getIntExtra("filme_id", -1);

        getSupportActionBar().setTitle("TÍTULO DO FILME......");

        Filme filme = new Filme(1, "Interstellar", "M3", "Ação, Aventura", "sinopse", "2h 32min", "25/11/2025", "Inglês", "John Lasseter", "http://10.0.2.2/CineLive/Web/frontend/web/uploads/posters/poster_6910b6ad1f9ea.jpg", "Em exibição");

        Glide.with(this)
                .load(filme.posterUrl)
                .placeholder(R.drawable.poster_placeholder)
                .diskCacheStrategy(DiskCacheStrategy.ALL)
                .into(binding.ivPoster);

        binding.tvTitulo.setText(filme.getTitulo());
        binding.tvRating.setText(filme.getRating());
        binding.tvGeneros.setText(filme.getGeneros());
        binding.tvEstreia.setText(filme.getEstreia());
        binding.tvDuracao.setText(filme.getDuracao());
        binding.tvIdioma.setText(filme.getIdioma());
        binding.tvRealizacao.setText(filme.getRealizacao());
        binding.tvSinopse.setText(filme.getSinopse());

        Map<String, List<Sessao>> sessoesPorData = new LinkedHashMap<>();

        sessoesPorData.put("28/11/2025", Arrays.asList(
                new Sessao(7, "28/11/2025", "16:00", "19:46"),
                new Sessao(8, "28/11/2025", "21:30", "21:16")
        ));

        sessoesPorData.put("29/11/2025", Arrays.asList(
                new Sessao(9, "29/11/2025", "14:00", "15:46"),
                new Sessao(10, "29/11/2025", "18:00", "19:46"),
                new Sessao(11, "29/11/2025", "22:00", "23:46")
        ));


        List<String> datas = new ArrayList<>(sessoesPorData.keySet());
        ArrayAdapter<String> dataAdapter = new ArrayAdapter<>(this, android.R.layout.simple_spinner_item, datas);
        binding.spinnerData.setAdapter(dataAdapter);

        binding.spinnerData.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                String data = datas.get(position);
                List<Sessao> sessoes = sessoesPorData.get(data);

                List<String> horas = new ArrayList<>();
                for (Sessao s : sessoes) {
                    horas.add(s.getHoraInicio());
                }

                ArrayAdapter<String> horaAdapter = new ArrayAdapter<>(DetalhesFilmeActivity.this, android.R.layout.simple_spinner_item, horas);
                binding.spinnerHora.setAdapter(horaAdapter);
            }

            @Override
            public void onNothingSelected(AdapterView<?> parent) {}
        });

        binding.spinnerHora.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {

                Sessao sessaoSelecionada = sessoesPorData
                        .get(binding.spinnerData.getSelectedItem().toString())
                        .get(position);

                binding.btnComprarBilhetes.setEnabled(true);
            }

            @Override
            public void onNothingSelected(AdapterView<?> parent) {}
        });

        binding.btnComprarBilhetes.setOnClickListener(v -> {
            Intent intentSessao = new Intent(this, ComprarBilhetesActivity.class);
            intentSessao.putExtra("sessao_id", sessaoSelecionada.getId());
            startActivity(intentSessao);
        });


    }

    @Override
    public boolean onSupportNavigateUp() {
        finish();
        return true;
    }
}